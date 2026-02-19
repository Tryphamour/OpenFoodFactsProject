<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Port;

interface UserPasswordHasher
{
    public function hash(string $plainPassword): string;
}

