<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Application\UseCase;

use App\IdentityAccess\Application\Port\UserAccountProvisioningGateway;
use App\IdentityAccess\Application\Port\UserPasswordHasher;
use App\IdentityAccess\Application\UseCase\CreateUser\CreateUserCommand;
use App\IdentityAccess\Application\UseCase\CreateUser\CreateUserHandler;
use App\IdentityAccess\Application\UseCase\CreateUser\InvalidUserData;
use App\IdentityAccess\Application\UseCase\CreateUser\UserEmailAlreadyExists;
use PHPUnit\Framework\TestCase;

final class CreateUserHandlerTest extends TestCase
{
    public function testCreatesUserWithHashedPasswordAndNormalizedEmail(): void
    {
        $gateway = new InMemoryUserAccountProvisioningGateway();
        $handler = new CreateUserHandler($gateway, new FakeUserPasswordHasher());

        $userId = $handler->handle(new CreateUserCommand(
            email: 'New.User@Example.COM',
            plainPassword: 'StrongPass1!',
            isAdmin: true,
        ));

        self::assertSame('user-1', $userId);
        self::assertSame('new.user@example.com', $gateway->created[0]['email']);
        self::assertSame('hash::StrongPass1!', $gateway->created[0]['passwordHash']);
        self::assertSame(['ROLE_ADMIN'], $gateway->created[0]['roles']);
    }

    public function testRejectsDuplicateEmail(): void
    {
        $gateway = new InMemoryUserAccountProvisioningGateway(['user@example.com']);
        $handler = new CreateUserHandler($gateway, new FakeUserPasswordHasher());

        $this->expectException(UserEmailAlreadyExists::class);
        $handler->handle(new CreateUserCommand('user@example.com', 'StrongPass1!'));
    }

    public function testRejectsInvalidData(): void
    {
        $handler = new CreateUserHandler(new InMemoryUserAccountProvisioningGateway(), new FakeUserPasswordHasher());

        $this->expectException(InvalidUserData::class);
        $handler->handle(new CreateUserCommand('not-an-email', 'short'));
    }
}

final class InMemoryUserAccountProvisioningGateway implements UserAccountProvisioningGateway
{
    /**
     * @var list<string>
     */
    private array $existingEmails;

    /**
     * @var list<array{email: string, passwordHash: string, roles: list<string>}>
     */
    public array $created = [];

    /**
     * @param list<string> $existingEmails
     */
    public function __construct(array $existingEmails = [])
    {
        $this->existingEmails = $existingEmails;
    }

    public function existsByEmail(string $email): bool
    {
        return \in_array(strtolower($email), $this->existingEmails, true);
    }

    public function create(string $email, string $passwordHash, array $roles): string
    {
        $this->created[] = [
            'email' => $email,
            'passwordHash' => $passwordHash,
            'roles' => $roles,
        ];

        return 'user-1';
    }
}

final class FakeUserPasswordHasher implements UserPasswordHasher
{
    public function hash(string $plainPassword): string
    {
        return sprintf('hash::%s', $plainPassword);
    }
}

