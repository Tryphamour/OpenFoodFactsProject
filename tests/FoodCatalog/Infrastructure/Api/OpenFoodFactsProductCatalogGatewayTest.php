<?php

declare(strict_types=1);

namespace App\Tests\FoodCatalog\Infrastructure\Api;

use App\FoodCatalog\Application\Port\ProductSearchQuery;
use App\FoodCatalog\Infrastructure\Api\OpenFoodFactsProductCatalogGateway;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class OpenFoodFactsProductCatalogGatewayTest extends TestCase
{
    public function testSearchMapsProductsAndAggregations(): void
    {
        $client = new MockHttpClient([
            new MockResponse(json_encode([
                'count' => 2,
                'products' => [
                    [
                        'code' => '123',
                        'product_name' => 'Milk',
                        'brands' => 'Acme',
                        'nutriscore_grade' => 'a',
                        'nova_group' => 1,
                        'image_front_small_url' => 'https://img.example/milk.jpg',
                    ],
                    [
                        'code' => '456',
                        'product_name' => 'Bread',
                        'brands' => 'Bakery',
                        'nutriscore_grade' => 'c',
                        'nova_group' => 3,
                        'image_front_small_url' => 'https://img.example/bread.jpg',
                    ],
                ],
            ], \JSON_THROW_ON_ERROR)),
        ]);

        $gateway = new OpenFoodFactsProductCatalogGateway(
            httpClient: $client,
            cachePool: new ArrayAdapter(),
            logger: new NullLogger(),
            baseUri: 'https://world.openfoodfacts.org',
            timeoutSeconds: 2,
            cacheTtlSeconds: 0,
            staleCacheTtlSeconds: 3600,
        );

        $result = $gateway->search(new ProductSearchQuery(
            term: 'test',
            filters: ['brand' => 'acme'],
            sortBy: 'name_asc',
        ));

        self::assertFalse($result->degraded);
        self::assertCount(1, $result->products);
        self::assertSame('Milk', $result->products[0]->name);
        self::assertSame(1, $result->aggregations['a']);
    }

    public function testSearchFallsBackToStaleCacheOnTransportFailure(): void
    {
        $cache = new ArrayAdapter();

        $primeGateway = new OpenFoodFactsProductCatalogGateway(
            httpClient: new MockHttpClient([
                new MockResponse(json_encode([
                    'count' => 1,
                    'products' => [[
                        'code' => '123',
                        'product_name' => 'Milk',
                        'brands' => 'Acme',
                        'nutriscore_grade' => 'a',
                    ]],
                ], \JSON_THROW_ON_ERROR)),
            ]),
            cachePool: $cache,
            logger: new NullLogger(),
            baseUri: 'https://world.openfoodfacts.org',
            timeoutSeconds: 2,
            cacheTtlSeconds: 300,
            staleCacheTtlSeconds: 3600,
        );
        $query = new ProductSearchQuery('milk');
        $primeGateway->search($query);
        $primaryCacheKey = 'off.search.'.sha1(json_encode([
            'term' => 'milk',
            'page' => 1,
            'limit' => 20,
            'filters' => [],
            'sortBy' => null,
        ], \JSON_THROW_ON_ERROR));
        $cache->deleteItem($primaryCacheKey);

        $failingClient = new MockHttpClient(static function (...$_): never {
            throw new TransportException('timeout');
        });
        $fallbackGateway = new OpenFoodFactsProductCatalogGateway(
            httpClient: $failingClient,
            cachePool: $cache,
            logger: new NullLogger(),
            baseUri: 'https://world.openfoodfacts.org',
            timeoutSeconds: 2,
            cacheTtlSeconds: 0,
            staleCacheTtlSeconds: 3600,
        );

        $result = $fallbackGateway->search($query);

        self::assertTrue($result->degraded);
        self::assertSame('stale_cache_fallback', $result->degradationReason);
        self::assertCount(1, $result->products);
        self::assertSame('Milk', $result->products[0]->name);
    }

    public function testSearchReturnsEmptyDegradedResultWhenNoFallbackExists(): void
    {
        $gateway = new OpenFoodFactsProductCatalogGateway(
            httpClient: new MockHttpClient(static function (...$_): never {
                throw new TransportException('timeout');
            }),
            cachePool: new ArrayAdapter(),
            logger: new NullLogger(),
            baseUri: 'https://world.openfoodfacts.org',
            timeoutSeconds: 2,
            cacheTtlSeconds: 300,
            staleCacheTtlSeconds: 3600,
        );

        $result = $gateway->search(new ProductSearchQuery('milk'));

        self::assertTrue($result->degraded);
        self::assertSame('off_unavailable', $result->degradationReason);
        self::assertCount(0, $result->products);
    }
}
