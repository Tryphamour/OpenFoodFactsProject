<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'security_users')]
#[ORM\UniqueConstraint(name: 'uniq_security_users_email', columns: ['email'])]
class SecurityUserRecord
{
    /**
     * @param list<string> $roles
     */
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'string', length: 64)]
        public string $id,
        #[ORM\Column(type: 'string', length: 180)]
        public string $email,
        #[ORM\Column(type: 'string', length: 255, name: 'password_hash')]
        public string $passwordHash,
        #[ORM\Column(type: 'json')]
        public array $roles,
        #[ORM\Column(type: 'integer', name: 'failed_attempts')]
        public int $failedAttempts = 0,
        #[ORM\Column(type: 'datetime_immutable', nullable: true, name: 'locked_until')]
        public ?\DateTimeImmutable $lockedUntil = null,
    ) {
    }
}

