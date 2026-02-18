<?php

declare(strict_types=1);

namespace App\Tests\Dashboard\UI;

use App\Dashboard\Infrastructure\Persistence\Doctrine\Entity\DashboardWidgetRecord;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\SecurityUserRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DashboardFlowTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private string $userId;
    private string $userEmail;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $dbPath = dirname(__DIR__, 4).'/var/dashboard_flow_test.sqlite';
        @unlink($dbPath);
        $_ENV['DATABASE_URL'] = 'sqlite:///'.$dbPath;
        $_SERVER['DATABASE_URL'] = 'sqlite:///'.$dbPath;
        $token = bin2hex(random_bytes(6));
        $this->userId = 'user_'.$token;
        $this->userEmail = sprintf('%s@example.com', $this->userId);

        $this->client = self::createClient();
        $this->entityManager = $this->client->getContainer()->get(EntityManagerInterface::class);
        $this->resetDatabase();
    }

    public function testDashboardPageRendersForAuthenticatedAndVerifiedUser(): void
    {
        $this->authenticateThrough2fa();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Dashboard');
        self::assertSelectorTextContains('h2', 'Widgets');
        self::assertSelectorExists('section[data-controller~="live"]');
    }

    public function testReorderEndpointPersistsNewWidgetOrder(): void
    {
        $this->entityManager->persist(new DashboardWidgetRecord(
            id: 'widget-1',
            ownerId: $this->userId,
            type: 'product_search',
            position: 0,
            configuration: [],
        ));
        $this->entityManager->persist(new DashboardWidgetRecord(
            id: 'widget-2',
            ownerId: $this->userId,
            type: 'nutriscore_distribution',
            position: 1,
            configuration: [],
        ));
        $this->entityManager->flush();

        $this->authenticateThrough2fa();
        $reorderToken = (string) $this->client
            ->getCrawler()
            ->filter('form[action="/dashboard/reorder"] input[name="_token"]')
            ->first()
            ->attr('value');

        $this->client->request('POST', '/dashboard/reorder', [
            '_token' => $reorderToken,
            'ordered_ids' => 'widget-2,widget-1',
        ]);

        self::assertResponseRedirects('/dashboard');
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();

        /** @var list<DashboardWidgetRecord> $records */
        $records = $this->entityManager
            ->getRepository(DashboardWidgetRecord::class)
            ->findBy(['ownerId' => $this->userId], ['position' => 'ASC']);

        self::assertSame('widget-2', $records[0]->id);
        self::assertSame('widget-1', $records[1]->id);
    }

    public function testDashboardStillRendersWhenCatalogGatewayIsDegraded(): void
    {
        $this->entityManager->persist(new DashboardWidgetRecord(
            id: 'widget-1',
            ownerId: $this->userId,
            type: 'product_search',
            position: 0,
            configuration: ['query' => 'milk'],
        ));
        $this->entityManager->flush();

        $this->authenticateThrough2fa();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('section', 'degraded: off_unavailable');
        self::assertSelectorExists('form[action="/dashboard/widget/widget-1/configure"]');
    }

    public function testAddWidgetEndpointPersistsWidgetWithoutManualReorder(): void
    {
        $this->authenticateThrough2fa();
        $addToken = (string) $this->client
            ->getCrawler()
            ->filter('form[action="/dashboard/widget/add"] input[name="_token"]')
            ->first()
            ->attr('value');

        $this->client->request('POST', '/dashboard/widget/add', [
            'type' => 'additives_overview',
            '_token' => $addToken,
        ]);

        self::assertResponseRedirects('/dashboard');
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();

        /** @var list<DashboardWidgetRecord> $records */
        $records = $this->entityManager
            ->getRepository(DashboardWidgetRecord::class)
            ->findBy(['ownerId' => $this->userId], ['position' => 'ASC']);

        self::assertCount(1, $records);
        self::assertSame('additives_overview', $records[0]->type);
    }

    public function testConfigureWidgetEndpointPersistsQuery(): void
    {
        $this->entityManager->persist(new DashboardWidgetRecord(
            id: 'widget-1',
            ownerId: $this->userId,
            type: 'product_search',
            position: 0,
            configuration: [],
        ));
        $this->entityManager->flush();

        $this->authenticateThrough2fa();
        $configureToken = (string) $this->client
            ->getCrawler()
            ->filter('form[action="/dashboard/widget/widget-1/configure"] input[name="_token"]')
            ->first()
            ->attr('value');

        $this->client->request('POST', '/dashboard/widget/widget-1/configure', [
            'query' => 'food',
            '_token' => $configureToken,
        ]);

        self::assertResponseRedirects('/dashboard');
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();

        /** @var DashboardWidgetRecord|null $record */
        $record = $this->entityManager
            ->getRepository(DashboardWidgetRecord::class)
            ->findOneBy(['ownerId' => $this->userId, 'id' => 'widget-1']);

        self::assertNotNull($record);
        self::assertSame(['query' => 'food'], $record->configuration);
    }

    private function authenticateThrough2fa(): void
    {
        $this->client->request('POST', '/login', [
            'email' => $this->userEmail,
            'password' => 'Admin1234!',
        ]);
        self::assertResponseRedirects('/2fa');
        $this->client->followRedirect();

        $this->client->request('POST', '/2fa', [
            'code' => '111111',
        ]);

        self::assertResponseRedirects('/dashboard');
        $this->client->followRedirect();
    }

    private function resetDatabase(): void
    {
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $tool = new SchemaTool($this->entityManager);
        $tool->dropSchema($metadata);
        $tool->createSchema($metadata);

        $this->entityManager->persist(new SecurityUserRecord(
            id: $this->userId,
            email: $this->userEmail,
            passwordHash: '$2y$12$1DhR3C6trehv1/qyG9DNPO.ffD8I/YMalDxrQZDVaJjFoIG3NT0Z6',
            roles: ['ROLE_ADMIN'],
            failedAttempts: 0,
            lockedUntil: null,
        ));
        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}
