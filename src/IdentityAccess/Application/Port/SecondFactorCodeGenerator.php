<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Port;

interface SecondFactorCodeGenerator
{
    public function generate(): string;
}

