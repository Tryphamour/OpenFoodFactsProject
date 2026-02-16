<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\UseCase\VerifySecondFactorChallenge;

final readonly class VerifySecondFactorChallengeCommand
{
    public function __construct(
        public string $challengeId,
        public string $submittedCode,
    ) {
    }
}

