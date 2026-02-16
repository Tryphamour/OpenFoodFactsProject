<?php

declare(strict_types=1);

namespace App\Dashboard\Application\UseCase\AddWidget;

final readonly class AddWidgetCommand
{
    /**
     * @param array<string, scalar> $configuration
     */
    public function __construct(
        public string $ownerId,
        public string $widgetType,
        public array $configuration = [],
    ) {
    }
}

