<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\UseCase\CreateUser;

use App\IdentityAccess\Application\Port\UserAccountProvisioningGateway;
use App\IdentityAccess\Application\Port\UserPasswordHasher;

final readonly class CreateUserHandler
{
    public function __construct(
        private UserAccountProvisioningGateway $provisioningGateway,
        private UserPasswordHasher $passwordHasher,
    ) {
    }

    public function handle(CreateUserCommand $command): string
    {
        $email = strtolower(trim($command->email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidUserData('A valid email is required.');
        }

        if (mb_strlen($command->plainPassword) < 10) {
            throw new InvalidUserData('Password must contain at least 10 characters.');
        }

        if ($this->provisioningGateway->existsByEmail($email)) {
            throw new UserEmailAlreadyExists(sprintf('A user already exists for "%s".', $email));
        }

        $roles = $command->isAdmin ? ['ROLE_ADMIN'] : [];
        $passwordHash = $this->passwordHasher->hash($command->plainPassword);

        return $this->provisioningGateway->create($email, $passwordHash, $roles);
    }
}

