<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Repository;

use App\IdentityAccess\Application\Port\UserAccountProvisioningGateway;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\SecurityUserRecord;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineUserAccountProvisioningGateway implements UserAccountProvisioningGateway
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function existsByEmail(string $email): bool
    {
        return $this->entityManager->getRepository(SecurityUserRecord::class)->count(['email' => strtolower($email)]) > 0;
    }

    public function create(string $email, string $passwordHash, array $roles): string
    {
        $userId = sprintf('user_%s', bin2hex(random_bytes(8)));
        $record = new SecurityUserRecord(
            id: $userId,
            email: strtolower($email),
            passwordHash: $passwordHash,
            roles: $roles,
            failedAttempts: 0,
            lockedUntil: null,
        );

        $this->entityManager->persist($record);
        $this->entityManager->flush();

        return $userId;
    }
}

