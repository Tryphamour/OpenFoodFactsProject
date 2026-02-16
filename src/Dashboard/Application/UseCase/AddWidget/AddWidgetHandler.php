<?php

declare(strict_types=1);

namespace App\Dashboard\Application\UseCase\AddWidget;

use App\Dashboard\Application\Port\DashboardRepository;
use App\Dashboard\Domain\Model\Widget\Widget;

final readonly class AddWidgetHandler
{
    public function __construct(private DashboardRepository $dashboards)
    {
    }

    public function handle(AddWidgetCommand $command): void
    {
        $dashboard = $this->dashboards->getForOwner($command->ownerId);
        $dashboard->addWidget(
            Widget::create(
                id: sprintf('widget_%s', bin2hex(random_bytes(8))),
                type: $command->widgetType,
                configuration: $command->configuration,
            ),
        );
        $this->dashboards->save($dashboard);
    }
}

