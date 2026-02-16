<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security;

use App\IdentityAccess\Application\Port\SecurityAuditTrail;
use Psr\Log\LoggerInterface;

final readonly class LoggerSecurityAuditTrail implements SecurityAuditTrail
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function record(string $eventName, array $metadata): void
    {
        $this->logger->info($eventName, $metadata);
    }
}
