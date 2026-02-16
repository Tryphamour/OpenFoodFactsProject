<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Application\UseCase;

use App\IdentityAccess\Application\Port\AccountLockPolicyProvider;
use App\IdentityAccess\Application\Port\SecurityAuditTrail;
use App\IdentityAccess\Application\Port\SecurityUserRepository;
use App\IdentityAccess\Application\UseCase\RecordFailedLoginAttempt\RecordFailedLoginAttemptCommand;
use App\IdentityAccess\Application\UseCase\RecordFailedLoginAttempt\RecordFailedLoginAttemptHandler;
use App\IdentityAccess\Domain\Model\User\AccountLockPolicy;
use App\IdentityAccess\Domain\Model\User\SecurityUser;
use PHPUnit\Framework\TestCase;

final class RecordFailedLoginAttemptHandlerTest extends TestCase
{
    public function testRecordsAuditWhenUserGetsLocked(): void
    {
        $user = new SecurityUser('user-1', 4);
        $repo = new InMemorySecurityUserRepository($user);
        $audit = new InMemorySecurityAuditTrail();
        $handler = new RecordFailedLoginAttemptHandler(
            $repo,
            new FixedAccountLockPolicyProvider(new AccountLockPolicy(5, new \DateInterval('PT15M'))),
            $audit,
        );

        $handler->handle(new RecordFailedLoginAttemptCommand('user-1', new \DateTimeImmutable('2026-02-16 12:00:00')));

        self::assertCount(1, $audit->events);
        self::assertSame('identity_access.account_locked', $audit->events[0]['event']);
    }

    public function testDoesNotRecordAuditWhenAlreadyLocked(): void
    {
        $user = new SecurityUser('user-1', 5, new \DateTimeImmutable('2026-02-16 12:15:00'));
        $repo = new InMemorySecurityUserRepository($user);
        $audit = new InMemorySecurityAuditTrail();
        $handler = new RecordFailedLoginAttemptHandler(
            $repo,
            new FixedAccountLockPolicyProvider(new AccountLockPolicy(5, new \DateInterval('PT15M'))),
            $audit,
        );

        $handler->handle(new RecordFailedLoginAttemptCommand('user-1', new \DateTimeImmutable('2026-02-16 12:01:00')));

        self::assertCount(0, $audit->events);
    }
}

final class InMemorySecurityUserRepository implements SecurityUserRepository
{
    public function __construct(private SecurityUser $user)
    {
    }

    public function getById(string $userId): SecurityUser
    {
        return $this->user;
    }

    public function save(SecurityUser $user): void
    {
        $this->user = $user;
    }
}

final readonly class FixedAccountLockPolicyProvider implements AccountLockPolicyProvider
{
    public function __construct(private AccountLockPolicy $policy)
    {
    }

    public function getPolicy(): AccountLockPolicy
    {
        return $this->policy;
    }
}

final class InMemorySecurityAuditTrail implements SecurityAuditTrail
{
    /**
     * @var list<array{event: string, metadata: array<string, scalar|null>}>
     */
    public array $events = [];

    public function record(string $eventName, array $metadata): void
    {
        $this->events[] = [
            'event' => $eventName,
            'metadata' => $metadata,
        ];
    }
}

