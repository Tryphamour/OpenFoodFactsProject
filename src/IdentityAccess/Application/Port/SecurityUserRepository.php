<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Port;

use App\IdentityAccess\Domain\Model\User\SecurityUser;

interface SecurityUserRepository
{
    public function getById(string $userId): SecurityUser;

    public function save(SecurityUser $user): void;
}

