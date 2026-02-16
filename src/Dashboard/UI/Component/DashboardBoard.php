<?php

declare(strict_types=1);

namespace App\Dashboard\UI\Component;

use App\Dashboard\Application\UseCase\AddWidget\AddWidgetCommand;
use App\Dashboard\Application\UseCase\AddWidget\AddWidgetHandler;
use App\Dashboard\Application\UseCase\ConfigureWidget\ConfigureWidgetCommand;
use App\Dashboard\Application\UseCase\ConfigureWidget\ConfigureWidgetHandler;
use App\Dashboard\Application\UseCase\GetDashboard\GetDashboardHandler;
use App\Dashboard\Application\UseCase\GetDashboard\GetDashboardQuery;
use App\Dashboard\Application\UseCase\RemoveWidget\RemoveWidgetCommand;
use App\Dashboard\Application\UseCase\RemoveWidget\RemoveWidgetHandler;
use App\IdentityAccess\Infrastructure\Security\AppUser;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('dashboard_board')]
final class DashboardBoard
{
    use DefaultActionTrait;

    public function __construct(
        private readonly Security $security,
        private readonly GetDashboardHandler $getDashboard,
        private readonly AddWidgetHandler $addWidget,
        private readonly RemoveWidgetHandler $removeWidget,
        private readonly ConfigureWidgetHandler $configureWidget,
    ) {
    }

    /**
     * @return list<array{id: string, type: string, position: int, configuration: array<string, scalar>}>
     */
    public function widgets(): array
    {
        $dashboard = $this->getDashboard->handle(new GetDashboardQuery($this->currentUserId()));

        return array_map(
            static fn ($widget): array => [
                'id' => $widget->id(),
                'type' => $widget->type(),
                'position' => $widget->position(),
                'configuration' => $widget->configuration(),
            ],
            $dashboard->widgets(),
        );
    }

    #[LiveAction]
    public function addWidget(#[LiveArg] string $type): void
    {
        $this->addWidget->handle(new AddWidgetCommand(
            ownerId: $this->currentUserId(),
            widgetType: $type,
        ));
    }

    #[LiveAction]
    public function removeWidget(#[LiveArg] string $widgetId): void
    {
        $this->removeWidget->handle(new RemoveWidgetCommand(
            ownerId: $this->currentUserId(),
            widgetId: $widgetId,
        ));
    }

    #[LiveAction]
    public function configureWidget(#[LiveArg] string $widgetId, #[LiveArg] string $query = ''): void
    {
        $configuration = [];
        if ($query !== '') {
            $configuration['query'] = $query;
        }

        $this->configureWidget->handle(new ConfigureWidgetCommand(
            ownerId: $this->currentUserId(),
            widgetId: $widgetId,
            configuration: $configuration,
        ));
    }

    private function currentUserId(): string
    {
        $user = $this->security->getUser();
        if (!$user instanceof AppUser) {
            throw new \RuntimeException('Authenticated AppUser is required.');
        }

        return $user->id();
    }
}
