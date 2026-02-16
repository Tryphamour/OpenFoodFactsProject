<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\UseCase\IssueSecondFactorChallenge;

use App\IdentityAccess\Application\Port\SecondFactorChallengeRepository;
use App\IdentityAccess\Application\Port\SecondFactorCodeGenerator;
use App\IdentityAccess\Application\Port\SecondFactorCodeSender;
use App\IdentityAccess\Application\Port\SecondFactorPolicyProvider;
use App\IdentityAccess\Domain\Model\TwoFactor\SecondFactorChallenge;
use App\Shared\Application\Clock\Clock;

final readonly class IssueSecondFactorChallengeHandler
{
    public function __construct(
        private SecondFactorChallengeRepository $challenges,
        private SecondFactorPolicyProvider $policyProvider,
        private SecondFactorCodeGenerator $codeGenerator,
        private SecondFactorCodeSender $codeSender,
        private Clock $clock,
    ) {
    }

    public function handle(IssueSecondFactorChallengeCommand $command): string
    {
        $code = $this->codeGenerator->generate();
        $challengeId = sprintf('2fa_%s', bin2hex(random_bytes(10)));
        $policy = $this->policyProvider->getPolicy();

        $challenge = SecondFactorChallenge::issue(
            id: $challengeId,
            userId: $command->userId,
            plainCode: $code,
            issuedAt: $this->clock->now(),
            timeToLive: $policy->timeToLive,
            maxAttempts: $policy->maxAttempts,
        );

        $this->challenges->save($challenge);
        $this->codeSender->send($command->email, $code);

        return $challengeId;
    }
}

