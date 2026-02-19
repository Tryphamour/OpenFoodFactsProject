<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security;

use App\IdentityAccess\Application\Port\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

final readonly class SymfonyUserPasswordHasher implements UserPasswordHasher
{
    public function __construct(private PasswordHasherFactoryInterface $passwordHasherFactory)
    {
    }

    public function hash(string $plainPassword): string
    {
        return $this->passwordHasherFactory
            ->getPasswordHasher(PasswordAuthenticatedUserInterface::class)
            ->hash($plainPassword);
    }
}

