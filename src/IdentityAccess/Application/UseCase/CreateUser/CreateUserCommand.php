<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\UseCase\CreateUser;

final readonly class CreateUserCommand
{
    public function __construct(
        public string $email,
        public string $plainPassword,
        public bool $isAdmin = false,
    ) {
    }
}

