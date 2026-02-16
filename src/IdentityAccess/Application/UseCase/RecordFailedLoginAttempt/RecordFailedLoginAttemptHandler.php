<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\UseCase\RecordFailedLoginAttempt;

use App\IdentityAccess\Application\Port\AccountLockPolicyProvider;
use App\IdentityAccess\Application\Port\SecurityAuditTrail;
use App\IdentityAccess\Application\Port\SecurityUserRepository;

final readonly class RecordFailedLoginAttemptHandler
{
    public function __construct(
        private SecurityUserRepository $users,
        private AccountLockPolicyProvider $lockPolicyProvider,
        private SecurityAuditTrail $auditTrail,
    ) {
    }

    public function handle(RecordFailedLoginAttemptCommand $command): void
    {
        $user = $this->users->getById($command->userId);
        $wasLocked = $user->isLockedAt($command->occurredAt);

        $user->recordFailedLogin($command->occurredAt, $this->lockPolicyProvider->getPolicy());

        $this->users->save($user);

        if (!$wasLocked && $user->isLockedAt($command->occurredAt)) {
            $this->auditTrail->record('identity_access.account_locked', [
                'user_id' => $user->id(),
                'locked_until' => $user->lockedUntil()?->format(\DateTimeInterface::ATOM),
            ]);
        }
    }
}

