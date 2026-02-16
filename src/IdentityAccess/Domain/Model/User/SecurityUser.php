<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Model\User;

final class SecurityUser
{
    private int $failedAttempts;

    private ?\DateTimeImmutable $lockedUntil;

    public function __construct(
        private readonly string $id,
        int $failedAttempts = 0,
        ?\DateTimeImmutable $lockedUntil = null,
    ) {
        if ($id === '') {
            throw new \InvalidArgumentException('User id cannot be empty.');
        }

        if ($failedAttempts < 0) {
            throw new \InvalidArgumentException('Failed attempts cannot be negative.');
        }

        $this->failedAttempts = $failedAttempts;
        $this->lockedUntil = $lockedUntil;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function recordFailedLogin(\DateTimeImmutable $occurredAt, AccountLockPolicy $policy): void
    {
        if ($this->isLockedAt($occurredAt)) {
            return;
        }

        $this->failedAttempts++;

        if ($this->failedAttempts >= $policy->maxFailedAttempts()) {
            $this->lockedUntil = $occurredAt->add($policy->lockDuration());
        }
    }

    public function recordSuccessfulLogin(): void
    {
        $this->failedAttempts = 0;
        $this->lockedUntil = null;
    }

    public function unlock(): void
    {
        $this->recordSuccessfulLogin();
    }

    public function isLockedAt(\DateTimeImmutable $now): bool
    {
        return $this->lockedUntil !== null && $this->lockedUntil > $now;
    }

    public function failedAttemptsCount(): int
    {
        return $this->failedAttempts;
    }

    public function lockedUntil(): ?\DateTimeImmutable
    {
        return $this->lockedUntil;
    }
}

