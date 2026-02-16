<?php

declare(strict_types=1);

namespace App\Dashboard\Application\UseCase\ReorderWidgets;

use App\Dashboard\Application\Port\DashboardRepository;

final readonly class ReorderWidgetsHandler
{
    public function __construct(private DashboardRepository $dashboards)
    {
    }

    public function handle(ReorderWidgetsCommand $command): void
    {
        $dashboard = $this->dashboards->getForOwner($command->ownerId);

        foreach ($command->orderedWidgetIds as $position => $widgetId) {
            $dashboard->moveWidget($widgetId, $position);
        }

        $this->dashboards->save($dashboard);
    }
}

