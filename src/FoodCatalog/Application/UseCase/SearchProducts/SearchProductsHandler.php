<?php

declare(strict_types=1);

namespace App\FoodCatalog\Application\UseCase\SearchProducts;

use App\FoodCatalog\Application\Port\ProductCatalogGateway;
use App\FoodCatalog\Application\Port\ProductSearchQuery;
use App\FoodCatalog\Application\Port\ProductSearchResult;

final readonly class SearchProductsHandler
{
    public function __construct(private ProductCatalogGateway $catalogGateway)
    {
    }

    public function handle(ProductSearchQuery $query): ProductSearchResult
    {
        return $this->catalogGateway->search($query);
    }
}
