<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Domain\Model\TwoFactor;

use App\IdentityAccess\Domain\Model\TwoFactor\SecondFactorChallenge;
use App\IdentityAccess\Domain\Model\TwoFactor\SecondFactorChallengeAlreadyVerified;
use App\IdentityAccess\Domain\Model\TwoFactor\SecondFactorChallengeExpired;
use App\IdentityAccess\Domain\Model\TwoFactor\SecondFactorChallengeTooManyAttempts;
use PHPUnit\Framework\TestCase;

final class SecondFactorChallengeTest extends TestCase
{
    public function testVerifiesCorrectCodeBeforeExpiration(): void
    {
        $challenge = SecondFactorChallenge::issue(
            'challenge-1',
            'user-1',
            '123456',
            new \DateTimeImmutable('2026-02-16 12:00:00'),
            new \DateInterval('PT10M'),
            5,
        );

        $result = $challenge->verify('123456', new \DateTimeImmutable('2026-02-16 12:05:00'));

        self::assertTrue($result);
        self::assertTrue($challenge->isVerified());
    }

    public function testWrongCodeIncrementsAttemptCounter(): void
    {
        $challenge = SecondFactorChallenge::issue(
            'challenge-1',
            'user-1',
            '123456',
            new \DateTimeImmutable('2026-02-16 12:00:00'),
            new \DateInterval('PT10M'),
            5,
        );

        $result = $challenge->verify('000000', new \DateTimeImmutable('2026-02-16 12:05:00'));

        self::assertFalse($result);
        self::assertSame(1, $challenge->attemptsCount());
    }

    public function testCannotVerifyExpiredChallenge(): void
    {
        $challenge = SecondFactorChallenge::issue(
            'challenge-1',
            'user-1',
            '123456',
            new \DateTimeImmutable('2026-02-16 12:00:00'),
            new \DateInterval('PT10M'),
            5,
        );

        $this->expectException(SecondFactorChallengeExpired::class);

        $challenge->verify('123456', new \DateTimeImmutable('2026-02-16 12:11:00'));
    }

    public function testCannotVerifyAfterTooManyAttempts(): void
    {
        $challenge = SecondFactorChallenge::issue(
            'challenge-1',
            'user-1',
            '123456',
            new \DateTimeImmutable('2026-02-16 12:00:00'),
            new \DateInterval('PT10M'),
            2,
        );

        $challenge->verify('000000', new \DateTimeImmutable('2026-02-16 12:01:00'));
        $challenge->verify('000000', new \DateTimeImmutable('2026-02-16 12:02:00'));

        $this->expectException(SecondFactorChallengeTooManyAttempts::class);

        $challenge->verify('123456', new \DateTimeImmutable('2026-02-16 12:03:00'));
    }

    public function testCannotVerifyTwice(): void
    {
        $challenge = SecondFactorChallenge::issue(
            'challenge-1',
            'user-1',
            '123456',
            new \DateTimeImmutable('2026-02-16 12:00:00'),
            new \DateInterval('PT10M'),
            5,
        );

        $challenge->verify('123456', new \DateTimeImmutable('2026-02-16 12:05:00'));

        $this->expectException(SecondFactorChallengeAlreadyVerified::class);

        $challenge->verify('123456', new \DateTimeImmutable('2026-02-16 12:06:00'));
    }
}
