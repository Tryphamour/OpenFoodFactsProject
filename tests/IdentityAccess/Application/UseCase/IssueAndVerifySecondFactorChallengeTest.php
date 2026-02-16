<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Application\UseCase;

use App\IdentityAccess\Application\Port\SecondFactorChallengeRepository;
use App\IdentityAccess\Application\Port\SecondFactorCodeGenerator;
use App\IdentityAccess\Application\Port\SecondFactorCodeSender;
use App\IdentityAccess\Application\Port\SecondFactorPolicy;
use App\IdentityAccess\Application\Port\SecondFactorPolicyProvider;
use App\IdentityAccess\Application\UseCase\IssueSecondFactorChallenge\IssueSecondFactorChallengeCommand;
use App\IdentityAccess\Application\UseCase\IssueSecondFactorChallenge\IssueSecondFactorChallengeHandler;
use App\IdentityAccess\Application\UseCase\VerifySecondFactorChallenge\VerifySecondFactorChallengeCommand;
use App\IdentityAccess\Application\UseCase\VerifySecondFactorChallenge\VerifySecondFactorChallengeHandler;
use App\IdentityAccess\Domain\Model\TwoFactor\SecondFactorChallenge;
use App\Shared\Application\Clock\Clock;
use PHPUnit\Framework\TestCase;

final class IssueAndVerifySecondFactorChallengeTest extends TestCase
{
    public function testIssueAndVerifyChallengeSuccess(): void
    {
        $repo = new InMemoryChallengeRepo();
        $sender = new CapturingCodeSender();
        $clock = new FixedClock(new \DateTimeImmutable('2026-02-16 14:00:00'));
        $issue = new IssueSecondFactorChallengeHandler(
            $repo,
            new FixedSecondFactorPolicyProvider(new SecondFactorPolicy(new \DateInterval('PT10M'), 5)),
            new FixedCodeGenerator('123456'),
            $sender,
            $clock,
        );

        $challengeId = $issue->handle(new IssueSecondFactorChallengeCommand('user-1', 'user@example.com'));

        self::assertNotSame('', $challengeId);
        self::assertSame('user@example.com', $sender->lastEmail);
        self::assertSame('123456', $sender->lastCode);

        $verify = new VerifySecondFactorChallengeHandler($repo, $clock);
        $ok = $verify->handle(new VerifySecondFactorChallengeCommand($challengeId, '123456'));

        self::assertTrue($ok);
    }
}

final readonly class FixedSecondFactorPolicyProvider implements SecondFactorPolicyProvider
{
    public function __construct(private SecondFactorPolicy $policy)
    {
    }

    public function getPolicy(): SecondFactorPolicy
    {
        return $this->policy;
    }
}

final readonly class FixedCodeGenerator implements SecondFactorCodeGenerator
{
    public function __construct(private string $code)
    {
    }

    public function generate(): string
    {
        return $this->code;
    }
}

final class CapturingCodeSender implements SecondFactorCodeSender
{
    public string $lastEmail = '';
    public string $lastCode = '';

    public function send(string $email, string $code): void
    {
        $this->lastEmail = $email;
        $this->lastCode = $code;
    }
}

final class InMemoryChallengeRepo implements SecondFactorChallengeRepository
{
    /**
     * @var array<string, SecondFactorChallenge>
     */
    private array $items = [];

    public function save(SecondFactorChallenge $challenge): void
    {
        $this->items[$challenge->id()] = $challenge;
    }

    public function getById(string $challengeId): SecondFactorChallenge
    {
        return $this->items[$challengeId];
    }
}

final readonly class FixedClock implements Clock
{
    public function __construct(private \DateTimeImmutable $now)
    {
    }

    public function now(): \DateTimeImmutable
    {
        return $this->now;
    }
}
