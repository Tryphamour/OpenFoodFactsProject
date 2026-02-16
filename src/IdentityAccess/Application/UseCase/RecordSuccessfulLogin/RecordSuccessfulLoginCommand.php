<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\UseCase\RecordSuccessfulLogin;

final readonly class RecordSuccessfulLoginCommand
{
    public function __construct(public string $userId)
    {
    }
}

