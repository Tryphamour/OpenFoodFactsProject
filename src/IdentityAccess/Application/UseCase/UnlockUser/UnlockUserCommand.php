<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\UseCase\UnlockUser;

final readonly class UnlockUserCommand
{
    public function __construct(
        public string $userId,
        public string $reason,
    ) {
    }
}

