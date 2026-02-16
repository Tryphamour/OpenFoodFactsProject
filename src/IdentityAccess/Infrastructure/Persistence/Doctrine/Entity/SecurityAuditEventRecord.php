<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'security_audit_events')]
#[ORM\Index(name: 'idx_security_audit_events_event_name', columns: ['event_name'])]
#[ORM\Index(name: 'idx_security_audit_events_occurred_at', columns: ['occurred_at'])]
class SecurityAuditEventRecord
{
    /**
     * @param array<string, scalar|null> $metadata
     */
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'string', length: 64)]
        public string $id,
        #[ORM\Column(type: 'string', length: 120, name: 'event_name')]
        public string $eventName,
        #[ORM\Column(type: 'json')]
        public array $metadata,
        #[ORM\Column(type: 'datetime_immutable', name: 'occurred_at')]
        public \DateTimeImmutable $occurredAt,
    ) {
    }
}

