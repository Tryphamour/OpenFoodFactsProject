<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Port;

interface UserAccountProvisioningGateway
{
    public function existsByEmail(string $email): bool;

    /**
     * @param list<string> $roles
     */
    public function create(string $email, string $passwordHash, array $roles): string;
}

