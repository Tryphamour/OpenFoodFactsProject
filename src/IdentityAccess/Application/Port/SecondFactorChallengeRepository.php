<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Port;

use App\IdentityAccess\Domain\Model\TwoFactor\SecondFactorChallenge;

interface SecondFactorChallengeRepository
{
    public function save(SecondFactorChallenge $challenge): void;

    public function getById(string $challengeId): SecondFactorChallenge;
}

