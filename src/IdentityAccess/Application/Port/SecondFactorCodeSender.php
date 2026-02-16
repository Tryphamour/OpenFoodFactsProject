<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Port;

interface SecondFactorCodeSender
{
    public function send(string $email, string $code): void;
}

