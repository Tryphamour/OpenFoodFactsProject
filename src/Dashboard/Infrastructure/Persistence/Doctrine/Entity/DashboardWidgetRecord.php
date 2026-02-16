<?php

declare(strict_types=1);

namespace App\Dashboard\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'dashboard_widgets')]
#[ORM\Index(name: 'idx_dashboard_widgets_owner_id', columns: ['owner_id'])]
#[ORM\UniqueConstraint(name: 'uniq_dashboard_widgets_owner_position', columns: ['owner_id', 'position'])]
class DashboardWidgetRecord
{
    /**
     * @param array<string, scalar> $configuration
     */
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'string', length: 64)]
        public string $id,
        #[ORM\Column(type: 'string', length: 64, name: 'owner_id')]
        public string $ownerId,
        #[ORM\Column(type: 'string', length: 64)]
        public string $type,
        #[ORM\Column(type: 'integer')]
        public int $position,
        #[ORM\Column(type: 'json')]
        public array $configuration = [],
    ) {
    }
}

