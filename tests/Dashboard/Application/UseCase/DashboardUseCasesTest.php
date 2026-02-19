<?php

declare(strict_types=1);

namespace App\Tests\Dashboard\Application\UseCase;

use App\Dashboard\Application\Port\DashboardRepository;
use App\Dashboard\Application\UseCase\AddWidget\AddWidgetCommand;
use App\Dashboard\Application\UseCase\AddWidget\AddWidgetHandler;
use App\Dashboard\Application\UseCase\ConfigureWidget\ConfigureWidgetCommand;
use App\Dashboard\Application\UseCase\ConfigureWidget\ConfigureWidgetHandler;
use App\Dashboard\Application\UseCase\GetDashboard\GetDashboardHandler;
use App\Dashboard\Application\UseCase\GetDashboard\GetDashboardQuery;
use App\Dashboard\Application\UseCase\RemoveWidget\RemoveWidgetCommand;
use App\Dashboard\Application\UseCase\RemoveWidget\RemoveWidgetHandler;
use App\Dashboard\Application\UseCase\ReorderWidgets\ReorderWidgetsCommand;
use App\Dashboard\Application\UseCase\ReorderWidgets\ReorderWidgetsHandler;
use App\Dashboard\Domain\Model\Dashboard;
use PHPUnit\Framework\TestCase;

final class DashboardUseCasesTest extends TestCase
{
    public function testAddConfigureRemoveAndReorderWidgets(): void
    {
        $repo = new InMemoryDashboardRepository();
        $add = new AddWidgetHandler($repo);
        $configure = new ConfigureWidgetHandler($repo);
        $remove = new RemoveWidgetHandler($repo);
        $reorder = new ReorderWidgetsHandler($repo);
        $get = new GetDashboardHandler($repo);

        $add->handle(new AddWidgetCommand('user-1', 'product_search', ['query' => 'milk']));
        $add->handle(new AddWidgetCommand('user-1', 'brand_search'));

        $dashboard = $get->handle(new GetDashboardQuery('user-1'));
        self::assertCount(2, $dashboard->widgets());

        $firstId = $dashboard->widgets()[0]->id();
        $secondId = $dashboard->widgets()[1]->id();

        $configure->handle(new ConfigureWidgetCommand('user-1', $firstId, ['query' => 'bread']));
        $reorder->handle(new ReorderWidgetsCommand('user-1', [$secondId, $firstId]));

        $dashboard = $get->handle(new GetDashboardQuery('user-1'));
        self::assertSame($secondId, $dashboard->widgets()[0]->id());
        self::assertSame('bread', $dashboard->widgets()[1]->configuration()['query']);

        $remove->handle(new RemoveWidgetCommand('user-1', $secondId));
        $dashboard = $get->handle(new GetDashboardQuery('user-1'));
        self::assertCount(1, $dashboard->widgets());
    }
}

final class InMemoryDashboardRepository implements DashboardRepository
{
    /**
     * @var array<string, Dashboard>
     */
    private array $items = [];

    public function getForOwner(string $ownerId): Dashboard
    {
        return $this->items[$ownerId] ?? Dashboard::createForOwner($ownerId);
    }

    public function save(Dashboard $dashboard): void
    {
        $this->items[$dashboard->ownerId()] = $dashboard;
    }
}
