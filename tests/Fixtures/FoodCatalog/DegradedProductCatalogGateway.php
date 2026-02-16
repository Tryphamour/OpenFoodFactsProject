<?php

declare(strict_types=1);

namespace App\Tests\Fixtures\FoodCatalog;

use App\FoodCatalog\Application\Port\ProductCatalogGateway;
use App\FoodCatalog\Application\Port\ProductSearchQuery;
use App\FoodCatalog\Application\Port\ProductSearchResult;

final class DegradedProductCatalogGateway implements ProductCatalogGateway
{
    public function search(ProductSearchQuery $query): ProductSearchResult
    {
        return ProductSearchResult::degraded(
            products: [],
            total: 0,
            page: $query->page,
            limit: $query->limit,
            aggregations: [],
            reason: 'off_unavailable',
        );
    }
}
