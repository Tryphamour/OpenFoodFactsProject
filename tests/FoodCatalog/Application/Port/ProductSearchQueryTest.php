<?php

declare(strict_types=1);

namespace App\Tests\FoodCatalog\Application\Port;

use App\FoodCatalog\Application\Port\ProductSearchQuery;
use PHPUnit\Framework\TestCase;

final class ProductSearchQueryTest extends TestCase
{
    public function testRejectsInvalidPage(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Search page must be >= 1.');

        new ProductSearchQuery(term: 'milk', page: 0);
    }

    public function testRejectsInvalidLimit(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Search limit must be between 1 and 50.');

        new ProductSearchQuery(term: 'milk', limit: 99);
    }

    public function testNormalizesFiltersByKeyOrder(): void
    {
        $query = new ProductSearchQuery(term: 'milk', filters: [
            'nutriscore' => 'a',
            'brand' => 'test-brand',
        ]);

        self::assertSame([
            'brand' => 'test-brand',
            'nutriscore' => 'a',
        ], $query->normalizedFilters());
    }
}
