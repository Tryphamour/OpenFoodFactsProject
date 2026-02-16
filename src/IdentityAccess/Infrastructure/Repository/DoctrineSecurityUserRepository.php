<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Repository;

use App\IdentityAccess\Application\Port\SecurityUserRepository;
use App\IdentityAccess\Domain\Model\User\SecurityUser;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\SecurityUserRecord;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineSecurityUserRepository implements SecurityUserRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function getById(string $userId): SecurityUser
    {
        $record = $this->entityManager->find(SecurityUserRecord::class, $userId);
        if (!$record instanceof SecurityUserRecord) {
            throw new \RuntimeException(sprintf('Security user "%s" not found.', $userId));
        }

        return new SecurityUser(
            id: $record->id,
            failedAttempts: $record->failedAttempts,
            lockedUntil: $record->lockedUntil,
        );
    }

    public function save(SecurityUser $user): void
    {
        $record = $this->entityManager->find(SecurityUserRecord::class, $user->id());
        if (!$record instanceof SecurityUserRecord) {
            throw new \RuntimeException(sprintf('Security user "%s" not found.', $user->id()));
        }

        $record->failedAttempts = $user->failedAttemptsCount();
        $record->lockedUntil = $user->lockedUntil();

        $this->entityManager->persist($record);
        $this->entityManager->flush();
    }
}

