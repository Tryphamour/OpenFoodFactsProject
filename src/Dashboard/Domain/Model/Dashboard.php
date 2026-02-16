<?php

declare(strict_types=1);

namespace App\Dashboard\Domain\Model;

use App\Dashboard\Domain\Model\Widget\Widget;

final class Dashboard
{
    /**
     * @var array<string, Widget>
     */
    private array $widgets = [];

    private function __construct(private readonly string $ownerId)
    {
        if ($ownerId === '') {
            throw new \InvalidArgumentException('Owner id cannot be empty.');
        }
    }

    public static function createForOwner(string $ownerId): self
    {
        return new self($ownerId);
    }

    /**
     * @param list<Widget> $widgets
     */
    public static function reconstitute(string $ownerId, array $widgets): self
    {
        $dashboard = new self($ownerId);
        foreach ($widgets as $widget) {
            $dashboard->widgets[$widget->id()] = $widget;
        }

        return $dashboard;
    }

    public function ownerId(): string
    {
        return $this->ownerId;
    }

    public function addWidget(Widget $widget): void
    {
        if (isset($this->widgets[$widget->id()])) {
            throw new \DomainException(sprintf('Widget "%s" already exists.', $widget->id()));
        }

        $widget->placeAt(\count($this->widgets));
        $this->widgets[$widget->id()] = $widget;
    }

    public function removeWidget(string $widgetId): void
    {
        if (!isset($this->widgets[$widgetId])) {
            return;
        }

        unset($this->widgets[$widgetId]);
        $this->normalizePositions();
    }

    public function moveWidget(string $widgetId, int $targetPosition): void
    {
        if (!isset($this->widgets[$widgetId])) {
            throw new \DomainException(sprintf('Widget "%s" not found.', $widgetId));
        }

        $ordered = $this->widgets();
        $targetPosition = max(0, min($targetPosition, \count($ordered) - 1));

        $moving = $this->widgets[$widgetId];
        $ordered = array_values(array_filter($ordered, static fn (Widget $w): bool => $w->id() !== $widgetId));
        array_splice($ordered, $targetPosition, 0, [$moving]);

        $this->widgets = [];
        foreach ($ordered as $index => $widget) {
            $widget->placeAt($index);
            $this->widgets[$widget->id()] = $widget;
        }
    }

    /**
     * @param array<string, scalar> $configuration
     */
    public function configureWidget(string $widgetId, array $configuration): void
    {
        if (!isset($this->widgets[$widgetId])) {
            throw new \DomainException(sprintf('Widget "%s" not found.', $widgetId));
        }

        $this->widgets[$widgetId]->configure($configuration);
    }

    /**
     * @return list<Widget>
     */
    public function widgets(): array
    {
        $widgets = array_values($this->widgets);
        usort($widgets, static fn (Widget $a, Widget $b): int => $a->position() <=> $b->position());

        return $widgets;
    }

    private function normalizePositions(): void
    {
        $ordered = $this->widgets();
        $this->widgets = [];
        foreach ($ordered as $index => $widget) {
            $widget->placeAt($index);
            $this->widgets[$widget->id()] = $widget;
        }
    }
}
