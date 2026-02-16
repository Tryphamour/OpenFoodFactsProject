<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Port;

use App\IdentityAccess\Domain\Model\User\AccountLockPolicy;

interface AccountLockPolicyProvider
{
    public function getPolicy(): AccountLockPolicy;
}

