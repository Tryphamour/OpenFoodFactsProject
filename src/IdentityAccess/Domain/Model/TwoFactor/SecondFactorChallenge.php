<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Model\TwoFactor;

final class SecondFactorChallenge
{
    private int $attempts;

    private ?\DateTimeImmutable $verifiedAt;

    private function __construct(
        private readonly string $id,
        private readonly string $userId,
        private readonly string $codeHash,
        private readonly \DateTimeImmutable $expiresAt,
        private readonly int $maxAttempts,
        int $attempts = 0,
        ?\DateTimeImmutable $verifiedAt = null,
    ) {
        if ($id === '') {
            throw new \InvalidArgumentException('Challenge id cannot be empty.');
        }

        if ($userId === '') {
            throw new \InvalidArgumentException('User id cannot be empty.');
        }

        if ($maxAttempts < 1) {
            throw new \InvalidArgumentException('Max attempts must be greater than zero.');
        }

        if ($attempts < 0) {
            throw new \InvalidArgumentException('Attempts cannot be negative.');
        }

        $this->attempts = $attempts;
        $this->verifiedAt = $verifiedAt;
    }

    public static function issue(
        string $id,
        string $userId,
        string $plainCode,
        \DateTimeImmutable $issuedAt,
        \DateInterval $timeToLive,
        int $maxAttempts,
    ): self {
        return new self(
            id: $id,
            userId: $userId,
            codeHash: password_hash($plainCode, PASSWORD_DEFAULT),
            expiresAt: $issuedAt->add($timeToLive),
            maxAttempts: $maxAttempts,
        );
    }

    public function verify(string $submittedCode, \DateTimeImmutable $now): bool
    {
        if ($this->verifiedAt !== null) {
            throw new SecondFactorChallengeAlreadyVerified('Challenge has already been verified.');
        }

        if ($this->expiresAt <= $now) {
            throw new SecondFactorChallengeExpired('Challenge has expired.');
        }

        if ($this->attempts >= $this->maxAttempts) {
            throw new SecondFactorChallengeTooManyAttempts('Maximum verification attempts reached.');
        }

        $this->attempts++;

        if (!password_verify($submittedCode, $this->codeHash)) {
            return false;
        }

        $this->verifiedAt = $now;

        return true;
    }

    public function isVerified(): bool
    {
        return $this->verifiedAt !== null;
    }

    public function attemptsCount(): int
    {
        return $this->attempts;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function expiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }
}
