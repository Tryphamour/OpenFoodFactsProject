<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\UseCase\UnlockUser;

use App\IdentityAccess\Application\Port\SecurityAuditTrail;
use App\IdentityAccess\Application\Port\SecurityUserRepository;

final readonly class UnlockUserHandler
{
    public function __construct(
        private SecurityUserRepository $users,
        private SecurityAuditTrail $auditTrail,
    ) {
    }

    public function handle(UnlockUserCommand $command): void
    {
        $user = $this->users->getById($command->userId);
        $user->unlock();

        $this->users->save($user);

        $this->auditTrail->record('identity_access.account_unlocked', [
            'user_id' => $user->id(),
            'reason' => $command->reason,
        ]);
    }
}
