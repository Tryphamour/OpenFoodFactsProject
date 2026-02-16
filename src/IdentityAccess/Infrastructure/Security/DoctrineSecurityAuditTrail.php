<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security;

use App\IdentityAccess\Application\Port\SecurityAuditTrail;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\SecurityAuditEventRecord;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineSecurityAuditTrail implements SecurityAuditTrail
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function record(string $eventName, array $metadata): void
    {
        $event = new SecurityAuditEventRecord(
            id: sprintf('audit_%s', bin2hex(random_bytes(10))),
            eventName: $eventName,
            metadata: $metadata,
            occurredAt: new \DateTimeImmutable(),
        );

        $this->entityManager->persist($event);
        $this->entityManager->flush();
    }
}
