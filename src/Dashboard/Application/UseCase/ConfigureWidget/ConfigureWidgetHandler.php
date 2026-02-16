<?php

declare(strict_types=1);

namespace App\Dashboard\Application\UseCase\ConfigureWidget;

use App\Dashboard\Application\Port\DashboardRepository;

final readonly class ConfigureWidgetHandler
{
    public function __construct(private DashboardRepository $dashboards)
    {
    }

    public function handle(ConfigureWidgetCommand $command): void
    {
        $dashboard = $this->dashboards->getForOwner($command->ownerId);
        $dashboard->configureWidget($command->widgetId, $command->configuration);
        $this->dashboards->save($dashboard);
    }
}

