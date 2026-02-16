<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Port;

final readonly class SecondFactorPolicy
{
    public function __construct(
        public \DateInterval $timeToLive,
        public int $maxAttempts,
    ) {
    }
}

interface SecondFactorPolicyProvider
{
    public function getPolicy(): SecondFactorPolicy;
}

