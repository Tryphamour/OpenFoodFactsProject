<?php

declare(strict_types=1);

namespace App\Dashboard\Infrastructure\Repository;

use App\Dashboard\Application\Port\DashboardRepository;
use App\Dashboard\Domain\Model\Dashboard;
use App\Dashboard\Domain\Model\Widget\Widget;
use App\Dashboard\Infrastructure\Persistence\Doctrine\Entity\DashboardWidgetRecord;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineDashboardRepository implements DashboardRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function getForOwner(string $ownerId): Dashboard
    {
        /** @var list<DashboardWidgetRecord> $records */
        $records = $this->entityManager->getRepository(DashboardWidgetRecord::class)
            ->findBy(['ownerId' => $ownerId], ['position' => 'ASC']);

        if ($records === []) {
            return Dashboard::createForOwner($ownerId);
        }

        $widgets = array_map(
            static fn (DashboardWidgetRecord $row): Widget => Widget::reconstitute(
                id: $row->id,
                type: $row->type,
                position: $row->position,
                configuration: $row->configuration,
            ),
            $records,
        );

        return Dashboard::reconstitute($ownerId, $widgets);
    }

    public function save(Dashboard $dashboard): void
    {
        /** @var list<DashboardWidgetRecord> $existing */
        $existing = $this->entityManager->getRepository(DashboardWidgetRecord::class)
            ->findBy(['ownerId' => $dashboard->ownerId()]);

        $existingById = [];
        foreach ($existing as $record) {
            $existingById[$record->id] = $record;
        }

        // Avoid transient UNIQUE(owner_id, position) collisions when widgets swap positions.
        foreach (array_values($existing) as $index => $record) {
            $record->position = 100000 + $index;
        }
        $this->entityManager->flush();

        foreach ($dashboard->widgets() as $widget) {
            $record = $existingById[$widget->id()] ?? new DashboardWidgetRecord(
                id: $widget->id(),
                ownerId: $dashboard->ownerId(),
                type: $widget->type(),
                position: $widget->position(),
                configuration: $widget->configuration(),
            );

            $record->type = $widget->type();
            $record->position = $widget->position();
            $record->configuration = $widget->configuration();

            $this->entityManager->persist($record);
            unset($existingById[$widget->id()]);
        }

        foreach ($existingById as $obsolete) {
            $this->entityManager->remove($obsolete);
        }

        $this->entityManager->flush();
    }
}
