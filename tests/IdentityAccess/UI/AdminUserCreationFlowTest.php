<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\UI;

use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\SecurityUserRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AdminUserCreationFlowTest extends WebTestCase
{
    private const string PASSWORD_HASH = '$2y$12$1DhR3C6trehv1/qyG9DNPO.ffD8I/YMalDxrQZDVaJjFoIG3NT0Z6';

    private EntityManagerInterface $entityManager;
    private KernelBrowser $client;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $dbPath = dirname(__DIR__, 4).'/var/admin_user_creation_flow.sqlite';
        @unlink($dbPath);
        $_ENV['DATABASE_URL'] = 'sqlite:///'.$dbPath;
        $_SERVER['DATABASE_URL'] = 'sqlite:///'.$dbPath;

        $this->client = self::createClient();
        $this->entityManager = $this->client->getContainer()->get(EntityManagerInterface::class);
        $this->resetDatabase();
    }

    public function testAdminCanCreateUserFromAdminInterface(): void
    {
        $this->authenticateThrough2fa('admin@example.com');

        $this->client->request('GET', '/admin/users/new');
        self::assertResponseIsSuccessful();

        $csrf = (string) $this->client->getCrawler()
            ->filter('form[action="/admin/users/new"] input[name="_token"]')
            ->first()
            ->attr('value');

        $this->client->request('POST', '/admin/users/new', [
            '_token' => $csrf,
            'email' => 'new.user@example.com',
            'password' => 'VeryStrong42!',
            'is_admin' => '1',
        ]);

        self::assertResponseRedirects('/admin/users/new');
        $this->client->followRedirect();
        self::assertSelectorTextContains('body', 'User account created.');

        /** @var SecurityUserRecord|null $record */
        $record = $this->entityManager
            ->getRepository(SecurityUserRecord::class)
            ->findOneBy(['email' => 'new.user@example.com']);

        self::assertNotNull($record);
        self::assertContains('ROLE_ADMIN', $record->roles);
    }

    public function testNonAdminCannotAccessAdminUserCreationInterface(): void
    {
        $this->authenticateThrough2fa('user@example.com');
        $this->client->request('GET', '/admin/users/new');

        self::assertResponseStatusCodeSame(403);
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
            id: 'user_admin_1',
            email: 'admin@example.com',
            passwordHash: self::PASSWORD_HASH,
            roles: ['ROLE_ADMIN'],
            failedAttempts: 0,
            lockedUntil: null,
        ));
        $this->entityManager->persist(new SecurityUserRecord(
            id: 'user_normal_1',
            email: 'user@example.com',
            passwordHash: self::PASSWORD_HASH,
            roles: [],
            failedAttempts: 0,
            lockedUntil: null,
        ));

        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}

