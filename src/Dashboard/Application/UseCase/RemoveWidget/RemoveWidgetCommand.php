<?php

declare(strict_types=1);

namespace App\Dashboard\Application\UseCase\RemoveWidget;

final readonly class RemoveWidgetCommand
{
    public function __construct(
        public string $ownerId,
        public string $widgetId,
    ) {
    }
}

