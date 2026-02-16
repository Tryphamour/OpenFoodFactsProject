<?php

declare(strict_types=1);

namespace App\Audit\Application\Port;

interface AuditTrail
{
    /**
     * @param array<string, scalar|null> $metadata
     */
    public function record(string $eventName, array $metadata): void;
}

