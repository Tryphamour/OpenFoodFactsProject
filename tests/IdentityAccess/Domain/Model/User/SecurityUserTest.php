<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Domain\Model\User;

use App\IdentityAccess\Domain\Model\User\AccountLockPolicy;
use App\IdentityAccess\Domain\Model\User\SecurityUser;
use PHPUnit\Framework\TestCase;

final class SecurityUserTest extends TestCase
{
    public function testLocksAccountAfterMaxFailedAttempts(): void
    {
        $policy = new AccountLockPolicy(5, new \DateInterval('PT15M'));
        $user = new SecurityUser('user-1');
        $now = new \DateTimeImmutable('2026-02-16 12:00:00');

        for ($i = 0; $i < 4; ++$i) {
            $user->recordFailedLogin($now, $policy);
        }

        self::assertFalse($user->isLockedAt($now));
        self::assertSame(4, $user->failedAttemptsCount());

        $user->recordFailedLogin($now, $policy);

        self::assertTrue($user->isLockedAt($now));
        self::assertSame(5, $user->failedAttemptsCount());
    }

    public function testSuccessfulLoginResetsSecurityCounters(): void
    {
        $policy = new AccountLockPolicy(5, new \DateInterval('PT15M'));
        $user = new SecurityUser('user-1');
        $now = new \DateTimeImmutable('2026-02-16 12:00:00');

        for ($i = 0; $i < 5; ++$i) {
            $user->recordFailedLogin($now, $policy);
        }

        self::assertTrue($user->isLockedAt($now));

        $user->recordSuccessfulLogin();

        self::assertFalse($user->isLockedAt($now));
        self::assertSame(0, $user->failedAttemptsCount());
    }

    public function testLockExpiresAfterPolicyDuration(): void
    {
        $policy = new AccountLockPolicy(5, new \DateInterval('PT15M'));
        $user = new SecurityUser('user-1');
        $lockedAt = new \DateTimeImmutable('2026-02-16 12:00:00');

        for ($i = 0; $i < 5; ++$i) {
            $user->recordFailedLogin($lockedAt, $policy);
        }

        self::assertTrue($user->isLockedAt($lockedAt->modify('+14 minutes')));
        self::assertFalse($user->isLockedAt($lockedAt->modify('+16 minutes')));
    }

    public function testManualUnlockClearsLockStateAndCounters(): void
    {
        $policy = new AccountLockPolicy(5, new \DateInterval('PT15M'));
        $user = new SecurityUser('user-1');
        $now = new \DateTimeImmutable('2026-02-16 12:00:00');

        for ($i = 0; $i < 5; ++$i) {
            $user->recordFailedLogin($now, $policy);
        }

        $user->unlock();

        self::assertFalse($user->isLockedAt($now));
        self::assertSame(0, $user->failedAttemptsCount());
    }
}

