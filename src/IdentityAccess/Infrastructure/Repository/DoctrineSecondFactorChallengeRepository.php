<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Repository;

use App\IdentityAccess\Application\Port\SecondFactorChallengeRepository;
use App\IdentityAccess\Domain\Model\TwoFactor\SecondFactorChallenge;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\SecondFactorChallengeRecord;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineSecondFactorChallengeRepository implements SecondFactorChallengeRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(SecondFactorChallenge $challenge): void
    {
        $record = $this->entityManager->find(SecondFactorChallengeRecord::class, $challenge->id());

        if (!$record instanceof SecondFactorChallengeRecord) {
            $record = new SecondFactorChallengeRecord(
                id: $challenge->id(),
                userId: $challenge->userId(),
                codeHash: $challenge->codeHash(),
                expiresAt: $challenge->expiresAt(),
                maxAttempts: $challenge->maxAttempts(),
                attempts: $challenge->attemptsCount(),
                verifiedAt: $challenge->verifiedAt(),
            );
        } else {
            $record->attempts = $challenge->attemptsCount();
            $record->verifiedAt = $challenge->verifiedAt();
            $record->expiresAt = $challenge->expiresAt();
            $record->maxAttempts = $challenge->maxAttempts();
        }

        $this->entityManager->persist($record);
        $this->entityManager->flush();
    }

    public function getById(string $challengeId): SecondFactorChallenge
    {
        $record = $this->entityManager->find(SecondFactorChallengeRecord::class, $challengeId);
        if (!$record instanceof SecondFactorChallengeRecord) {
            throw new \RuntimeException(sprintf('2FA challenge "%s" not found.', $challengeId));
        }

        return SecondFactorChallenge::reconstitute(
            id: $record->id,
            userId: $record->userId,
            codeHash: $record->codeHash,
            expiresAt: $record->expiresAt,
            maxAttempts: $record->maxAttempts,
            attempts: $record->attempts,
            verifiedAt: $record->verifiedAt,
        );
    }
}

