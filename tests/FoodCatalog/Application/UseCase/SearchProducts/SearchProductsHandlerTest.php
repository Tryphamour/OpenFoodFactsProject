<?php

declare(strict_types=1);

namespace App\Tests\FoodCatalog\Application\UseCase\SearchProducts;

use App\FoodCatalog\Application\Port\ProductCatalogGateway;
use App\FoodCatalog\Application\Port\ProductSearchQuery;
use App\FoodCatalog\Application\Port\ProductSearchResult;
use App\FoodCatalog\Application\Port\ProductView;
use App\FoodCatalog\Application\UseCase\SearchProducts\SearchProductsHandler;
use PHPUnit\Framework\TestCase;

final class SearchProductsHandlerTest extends TestCase
{
    public function testDelegatesToGatewayAndReturnsResult(): void
    {
        $expectedResult = ProductSearchResult::success(
            products: [new ProductView('123', 'Milk', 'Brand', 'a', 1, null)],
            total: 1,
            page: 1,
            limit: 20,
            aggregations: ['a' => 1],
        );

        $gateway = new class($expectedResult) implements ProductCatalogGateway {
            public function __construct(private readonly ProductSearchResult $result)
            {
            }

            public function search(ProductSearchQuery $query): ProductSearchResult
            {
                return $this->result;
            }
        };

        $handler = new SearchProductsHandler($gateway);
        $result = $handler->handle(new ProductSearchQuery('milk'));

        self::assertSame($expectedResult, $result);
    }
}
