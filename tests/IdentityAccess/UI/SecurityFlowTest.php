<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\UI;

use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\SecurityUserRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SecurityFlowTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private KernelBrowser $client;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = self::createClient();
        $this->entityManager = $this->client->getContainer()->get(EntityManagerInterface::class);
        $this->resetDatabase();
    }

    public function testSuccessfulLoginThenSecondFactorVerification(): void
    {
        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        $this->client->request('POST', '/login', [
            'email' => 'admin@example.com',
            'password' => 'Admin1234!',
        ]);

        self::assertResponseRedirects('/2fa');
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();

        $this->client->request('POST', '/2fa', [
            'code' => '111111',
        ]);

        self::assertResponseRedirects('/dashboard');
        $this->client->followRedirect();
        self::assertSelectorTextContains('h1', 'Dashboard');
    }

    public function testAccountLocksAfterFiveFailedAttempts(): void
    {
        for ($i = 0; $i < 5; ++$i) {
            $this->client->request('POST', '/login', [
                'email' => 'admin@example.com',
                'password' => 'WrongPassword!',
            ]);
            self::assertResponseRedirects('/login');
            $this->client->followRedirect();
        }

        $user = $this->entityManager->find(SecurityUserRecord::class, 'user_admin_1');
        self::assertInstanceOf(SecurityUserRecord::class, $user);
        self::assertSame(5, $user->failedAttempts);
        self::assertNotNull($user->lockedUntil);
    }

    private function resetDatabase(): void
    {
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $tool = new SchemaTool($this->entityManager);
        $tool->dropSchema($metadata);
        $tool->createSchema($metadata);

        $this->entityManager->persist(new SecurityUserRecord(
            id: 'user_admin_1',
            email: 'admin@example.com',
            passwordHash: '$2y$12$1DhR3C6trehv1/qyG9DNPO.ffD8I/YMalDxrQZDVaJjFoIG3NT0Z6',
            roles: ['ROLE_ADMIN'],
            failedAttempts: 0,
            lockedUntil: null,
        ));
        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}
