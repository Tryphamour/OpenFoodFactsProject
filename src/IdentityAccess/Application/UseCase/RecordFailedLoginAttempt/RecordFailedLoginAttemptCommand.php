<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\UseCase\RecordFailedLoginAttempt;

final readonly class RecordFailedLoginAttemptCommand
{
    public function __construct(
        public string $userId,
        public \DateTimeImmutable $occurredAt,
    ) {
    }
}

