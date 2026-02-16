<?php

declare(strict_types=1);

namespace App\Tests\Dashboard\Domain\Model;

use App\Dashboard\Domain\Model\Dashboard;
use App\Dashboard\Domain\Model\Widget\Widget;
use PHPUnit\Framework\TestCase;

final class DashboardTest extends TestCase
{
    public function testAddWidgetAppendsToLayout(): void
    {
        $dashboard = Dashboard::createForOwner('user-1');

        $widget = Widget::create('widget-1', 'product_search', ['query' => 'milk']);
        $dashboard->addWidget($widget);

        self::assertCount(1, $dashboard->widgets());
        self::assertSame('widget-1', $dashboard->widgets()[0]->id());
        self::assertSame(0, $dashboard->widgets()[0]->position());
    }

    public function testCannotAddDuplicateWidgetId(): void
    {
        $dashboard = Dashboard::createForOwner('user-1');
        $dashboard->addWidget(Widget::create('widget-1', 'product_search'));

        $this->expectException(\DomainException::class);

        $dashboard->addWidget(Widget::create('widget-1', 'nutriscore_distribution'));
    }

    public function testRemoveWidgetReordersRemainingWidgets(): void
    {
        $dashboard = Dashboard::createForOwner('user-1');
        $dashboard->addWidget(Widget::create('widget-1', 'product_search'));
        $dashboard->addWidget(Widget::create('widget-2', 'nutriscore_distribution'));
        $dashboard->addWidget(Widget::create('widget-3', 'additives_overview'));

        $dashboard->removeWidget('widget-2');

        self::assertCount(2, $dashboard->widgets());
        self::assertSame('widget-1', $dashboard->widgets()[0]->id());
        self::assertSame(0, $dashboard->widgets()[0]->position());
        self::assertSame('widget-3', $dashboard->widgets()[1]->id());
        self::assertSame(1, $dashboard->widgets()[1]->position());
    }

    public function testMoveWidgetChangesOrderAndNormalizesPositions(): void
    {
        $dashboard = Dashboard::createForOwner('user-1');
        $dashboard->addWidget(Widget::create('widget-1', 'product_search'));
        $dashboard->addWidget(Widget::create('widget-2', 'nutriscore_distribution'));
        $dashboard->addWidget(Widget::create('widget-3', 'additives_overview'));

        $dashboard->moveWidget('widget-1', 2);

        self::assertSame('widget-2', $dashboard->widgets()[0]->id());
        self::assertSame('widget-3', $dashboard->widgets()[1]->id());
        self::assertSame('widget-1', $dashboard->widgets()[2]->id());
        self::assertSame(0, $dashboard->widgets()[0]->position());
        self::assertSame(1, $dashboard->widgets()[1]->position());
        self::assertSame(2, $dashboard->widgets()[2]->position());
    }

    public function testConfigureWidgetUpdatesConfiguration(): void
    {
        $dashboard = Dashboard::createForOwner('user-1');
        $dashboard->addWidget(Widget::create('widget-1', 'product_search', ['query' => 'milk']));

        $dashboard->configureWidget('widget-1', ['query' => 'bread', 'sort' => 'name']);

        self::assertSame(['query' => 'bread', 'sort' => 'name'], $dashboard->widgets()[0]->configuration());
    }
}

