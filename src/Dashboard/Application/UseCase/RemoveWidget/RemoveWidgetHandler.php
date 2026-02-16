<?php

declare(strict_types=1);

namespace App\Dashboard\Application\UseCase\RemoveWidget;

use App\Dashboard\Application\Port\DashboardRepository;

final readonly class RemoveWidgetHandler
{
    public function __construct(private DashboardRepository $dashboards)
    {
    }

    public function handle(RemoveWidgetCommand $command): void
    {
        $dashboard = $this->dashboards->getForOwner($command->ownerId);
        $dashboard->removeWidget($command->widgetId);
        $this->dashboards->save($dashboard);
    }
}

