<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security;

use App\IdentityAccess\Application\Port\SecondFactorCodeGenerator;

final readonly class StaticSecondFactorCodeGenerator implements SecondFactorCodeGenerator
{
    public function __construct(private string $code = '111111')
    {
    }

    public function generate(): string
    {
        return $this->code;
    }
}

