<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security;

use App\IdentityAccess\Application\Port\AccountLockPolicyProvider;
use App\IdentityAccess\Domain\Model\User\AccountLockPolicy;

final readonly class FixedAccountLockPolicyProvider implements AccountLockPolicyProvider
{
    public function __construct(
        private int $maxFailedAttempts = 5,
        private string $lockDurationSpec = 'PT15M',
    ) {
    }

    public function getPolicy(): AccountLockPolicy
    {
        return new AccountLockPolicy(
            maxFailedAttempts: $this->maxFailedAttempts,
            lockDuration: new \DateInterval($this->lockDurationSpec),
        );
    }
}

