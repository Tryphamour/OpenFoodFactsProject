<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\UseCase\IssueSecondFactorChallenge;

final readonly class IssueSecondFactorChallengeCommand
{
    public function __construct(
        public string $userId,
        public string $email,
    ) {
    }
}

