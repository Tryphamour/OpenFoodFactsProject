<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security;

use App\IdentityAccess\Application\Port\SecondFactorPolicy;
use App\IdentityAccess\Application\Port\SecondFactorPolicyProvider;

final readonly class FixedSecondFactorPolicyProvider implements SecondFactorPolicyProvider
{
    public function __construct(
        private string $ttlSpec = 'PT10M',
        private int $maxAttempts = 5,
    ) {
    }

    public function getPolicy(): SecondFactorPolicy
    {
        return new SecondFactorPolicy(new \DateInterval($this->ttlSpec), $this->maxAttempts);
    }
}

