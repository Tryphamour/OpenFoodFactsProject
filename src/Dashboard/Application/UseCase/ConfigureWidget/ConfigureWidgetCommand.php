<?php

declare(strict_types=1);

namespace App\Dashboard\Application\UseCase\ConfigureWidget;

final readonly class ConfigureWidgetCommand
{
    /**
     * @param array<string, scalar> $configuration
     */
    public function __construct(
        public string $ownerId,
        public string $widgetId,
        public array $configuration,
    ) {
    }
}

