<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Repository;

use App\IdentityAccess\Application\Port\SecondFactorChallengeRepository;
use App\IdentityAccess\Domain\Model\TwoFactor\SecondFactorChallenge;
use App\IdentityAccess\Infrastructure\Security\InMemoryIdentityStore;

final readonly class InMemorySecondFactorChallengeRepository implements SecondFactorChallengeRepository
{
    public function __construct(private InMemoryIdentityStore $store)
    {
    }

    public function save(SecondFactorChallenge $challenge): void
    {
        $this->store->saveChallenge($challenge);
    }

    public function getById(string $challengeId): SecondFactorChallenge
    {
        $challenge = $this->store->getChallengeById($challengeId);
        if ($challenge === null) {
            throw new \RuntimeException(sprintf('2FA challenge "%s" not found.', $challengeId));
        }

        return $challenge;
    }
}

