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
use App\FoodCatalog\Application\Port\ProductSearchQuery;
use App\FoodCatalog\Application\UseCase\SearchProducts\SearchProductsHandler;
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
        private readonly SearchProductsHandler $searchProducts,
    ) {
    }

    /**
     * @return list<array{
     *     id: string,
     *     type: string,
     *     position: int,
     *     configuration: array<string, scalar>,
     *     preview: list<array{name: string, brand: ?string, nutriScore: ?string}>,
     *     degraded: bool,
     *     degradationReason: ?string
     * }>
     */
    public function widgets(): array
    {
        $dashboard = $this->getDashboard->handle(new GetDashboardQuery($this->currentUserId()));

        return array_map(
            function ($widget): array {
                $preview = [];
                $degraded = false;
                $degradationReason = null;

                if ($widget->type() === 'product_search') {
                    $query = (string) ($widget->configuration()['query'] ?? '');
                    if ($query !== '') {
                        $searchResult = $this->searchProducts->handle(new ProductSearchQuery(
                            term: $query,
                            page: 1,
                            limit: 5,
                            sortBy: 'name_asc',
                        ));

                        $preview = array_map(
                            static fn ($product): array => [
                                'name' => $product->name,
                                'brand' => $product->brand,
                                'nutriScore' => $product->nutriScore,
                            ],
                            $searchResult->products,
                        );
                        $degraded = $searchResult->degraded;
                        $degradationReason = $searchResult->degradationReason;
                    }
                }

                return [
                    'id' => $widget->id(),
                    'type' => $widget->type(),
                    'position' => $widget->position(),
                    'configuration' => $widget->configuration(),
                    'preview' => $preview,
                    'degraded' => $degraded,
                    'degradationReason' => $degradationReason,
                ];
            },
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
