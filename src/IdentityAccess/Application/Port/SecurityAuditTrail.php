<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Port;

interface SecurityAuditTrail
{
    /**
     * @param array<string, scalar|null> $metadata
     */
    public function record(string $eventName, array $metadata): void;
}

