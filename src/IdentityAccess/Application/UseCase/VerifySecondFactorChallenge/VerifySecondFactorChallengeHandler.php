<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\UseCase\VerifySecondFactorChallenge;

use App\IdentityAccess\Application\Port\SecondFactorChallengeRepository;
use App\Shared\Application\Clock\Clock;

final readonly class VerifySecondFactorChallengeHandler
{
    public function __construct(
        private SecondFactorChallengeRepository $challenges,
        private Clock $clock,
    ) {
    }

    public function handle(VerifySecondFactorChallengeCommand $command): bool
    {
        $challenge = $this->challenges->getById($command->challengeId);
        $result = $challenge->verify($command->submittedCode, $this->clock->now());
        $this->challenges->save($challenge);

        return $result;
    }
}
