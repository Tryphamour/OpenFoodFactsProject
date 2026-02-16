<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Application\UseCase;

use App\IdentityAccess\Application\UseCase\UnlockUser\UnlockUserCommand;
use App\IdentityAccess\Application\UseCase\UnlockUser\UnlockUserHandler;
use App\IdentityAccess\Domain\Model\User\SecurityUser;
use PHPUnit\Framework\TestCase;

final class UnlockUserHandlerTest extends TestCase
{
    public function testUnlocksUserAndWritesAuditEvent(): void
    {
        $repo = new InMemorySecurityUserRepository(
            new SecurityUser('user-1', 5, new \DateTimeImmutable('2026-02-16 12:30:00')),
        );
        $audit = new InMemorySecurityAuditTrail();
        $handler = new UnlockUserHandler($repo, $audit);

        $handler->handle(new UnlockUserCommand('user-1', 'admin_manual_unlock'));

        self::assertCount(1, $audit->events);
        self::assertSame('identity_access.account_unlocked', $audit->events[0]['event']);
    }
}
