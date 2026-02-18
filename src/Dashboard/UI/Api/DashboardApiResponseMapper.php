<?php

declare(strict_types=1);

namespace App\Dashboard\UI\Api;

use App\Dashboard\Domain\Model\Dashboard;
use App\Dashboard\Domain\Model\Widget\Widget;

final class DashboardApiResponseMapper
{
    /**
     * @return array{
     *     ownerId: string,
     *     widgets: list<array{
     *         id: string,
     *         type: string,
     *         position: int,
     *         configuration: array<string, scalar>
     *     }>
     * }
     */
    public function map(Dashboard $dashboard): array
    {
        return [
            'ownerId' => $dashboard->ownerId(),
            'widgets' => array_map(
                static fn (Widget $widget): array => [
                    'id' => $widget->id(),
                    'type' => $widget->type(),
                    'position' => $widget->position(),
                    'configuration' => $widget->configuration(),
                ],
                $dashboard->widgets(),
            ),
        ];
    }
}
