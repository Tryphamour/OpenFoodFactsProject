<?php

declare(strict_types=1);

namespace App\Tests\Dashboard\UI;

use App\Dashboard\Infrastructure\Persistence\Doctrine\Entity\DashboardWidgetRecord;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\SecurityUserRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class InternalDashboardApiTest extends WebTestCase
{
    private const string PASSWORD_HASH = '$2y$12$1DhR3C6trehv1/qyG9DNPO.ffD8I/YMalDxrQZDVaJjFoIG3NT0Z6';

    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private string $ownerUserId;
    private string $requesterUserId;
    private string $adminUserId;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $dbPath = dirname(__DIR__, 4).'/var/internal_dashboard_api_test.sqlite';
        @unlink($dbPath);
        $_ENV['DATABASE_URL'] = 'sqlite:///'.$dbPath;
        $_SERVER['DATABASE_URL'] = 'sqlite:///'.$dbPath;

        $token = bin2hex(random_bytes(6));
        $this->ownerUserId = 'owner_'.$token;
        $this->requesterUserId = 'requester_'.$token;
        $this->adminUserId = 'admin_'.$token;

        $this->client = self::createClient();
        $this->entityManager = $this->client->getContainer()->get(EntityManagerInterface::class);
        $this->resetDatabase();
    }

    public function testOwnerCanReadOwnDashboardThroughInternalApi(): void
    {
        $this->authenticateThrough2fa(sprintf('%s@example.com', $this->ownerUserId));

        $this->client->request('GET', sprintf('/internal/api/dashboard/%s', $this->ownerUserId));

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertStringContainsString('application/json', (string) $this->client->getResponse()->headers->get('content-type'));
        $payload = json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame($this->ownerUserId, $payload['data']['ownerId']);
        self::assertCount(2, $payload['data']['widgets']);
        self::assertSame('widget-1', $payload['data']['widgets'][0]['id']);
        self::assertSame('product_search', $payload['data']['widgets'][0]['type']);
        self::assertSame(['query' => 'milk'], $payload['data']['widgets'][0]['configuration']);
    }

    public function testNonOwnerNonAdminIsDeniedWithProblemDetails(): void
    {
        $this->authenticateThrough2fa(sprintf('%s@example.com', $this->requesterUserId));

        $this->client->request('GET', sprintf('/internal/api/dashboard/%s', $this->ownerUserId));

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        self::assertStringContainsString('application/problem+json', (string) $this->client->getResponse()->headers->get('content-type'));
        $payload = json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('about:blank', $payload['type']);
        self::assertSame('Forbidden', $payload['title']);
        self::assertSame(403, $payload['status']);
        self::assertSame(sprintf('/internal/api/dashboard/%s', $this->ownerUserId), $payload['instance']);
        self::assertIsString($payload['traceId']);
        self::assertNotSame('', $payload['traceId']);
    }

    public function testAdminCanReadAnotherUserDashboard(): void
    {
        $this->authenticateThrough2fa(sprintf('%s@example.com', $this->adminUserId));

        $this->client->request('GET', sprintf('/internal/api/dashboard/%s', $this->ownerUserId));

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $payload = json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame($this->ownerUserId, $payload['data']['ownerId']);
    }

    private function authenticateThrough2fa(string $email): void
    {
        $this->client->request('POST', '/login', [
            'email' => $email,
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
            id: $this->ownerUserId,
            email: sprintf('%s@example.com', $this->ownerUserId),
            passwordHash: self::PASSWORD_HASH,
            roles: [],
            failedAttempts: 0,
            lockedUntil: null,
        ));
        $this->entityManager->persist(new SecurityUserRecord(
            id: $this->requesterUserId,
            email: sprintf('%s@example.com', $this->requesterUserId),
            passwordHash: self::PASSWORD_HASH,
            roles: [],
            failedAttempts: 0,
            lockedUntil: null,
        ));
        $this->entityManager->persist(new SecurityUserRecord(
            id: $this->adminUserId,
            email: sprintf('%s@example.com', $this->adminUserId),
            passwordHash: self::PASSWORD_HASH,
            roles: ['ROLE_ADMIN'],
            failedAttempts: 0,
            lockedUntil: null,
        ));
        $this->entityManager->persist(new DashboardWidgetRecord(
            id: 'widget-1',
            ownerId: $this->ownerUserId,
            type: 'product_search',
            position: 0,
            configuration: ['query' => 'milk'],
        ));
        $this->entityManager->persist(new DashboardWidgetRecord(
            id: 'widget-2',
            ownerId: $this->ownerUserId,
            type: 'brand_search',
            position: 1,
            configuration: [],
        ));

        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}
