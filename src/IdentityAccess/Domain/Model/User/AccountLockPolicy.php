<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Model\User;

final readonly class AccountLockPolicy
{
    public function __construct(
        private int $maxFailedAttempts,
        private \DateInterval $lockDuration,
    ) {
        if ($maxFailedAttempts < 1) {
            throw new \InvalidArgumentException('Max failed attempts must be greater than zero.');
        }
    }

    public function maxFailedAttempts(): int
    {
        return $this->maxFailedAttempts;
    }

    public function lockDuration(): \DateInterval
    {
        return $this->lockDuration;
    }
}

