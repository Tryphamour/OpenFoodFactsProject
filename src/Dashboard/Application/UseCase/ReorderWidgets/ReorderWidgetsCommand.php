<?php

declare(strict_types=1);

namespace App\Dashboard\Application\UseCase\ReorderWidgets;

final readonly class ReorderWidgetsCommand
{
    /**
     * @param list<string> $orderedWidgetIds
     */
    public function __construct(
        public string $ownerId,
        public array $orderedWidgetIds,
    ) {
    }
}

