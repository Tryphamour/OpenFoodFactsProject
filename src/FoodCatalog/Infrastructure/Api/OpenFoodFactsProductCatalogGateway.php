<?php

declare(strict_types=1);

namespace App\FoodCatalog\Infrastructure\Api;

use App\FoodCatalog\Application\Port\ProductCatalogGateway;
use App\FoodCatalog\Application\Port\ProductSearchQuery;
use App\FoodCatalog\Application\Port\ProductSearchResult;
use App\FoodCatalog\Application\Port\ProductView;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class OpenFoodFactsProductCatalogGateway implements ProductCatalogGateway
{
    public function __construct(
        #[Autowire(service: 'http_client')]
        private HttpClientInterface $httpClient,
        #[Autowire(service: 'cache.app')]
        private CacheItemPoolInterface $cachePool,
        private LoggerInterface $logger,
        private string $baseUri,
        private int $timeoutSeconds,
        private int $cacheTtlSeconds,
        private int $staleCacheTtlSeconds,
    ) {
    }

    public function search(ProductSearchQuery $query): ProductSearchResult
    {
        $cacheHash = sha1($this->normalizedCachePayload($query));
        $cacheKey = 'off.search.'.$cacheHash;
        $staleKey = 'off.search.stale.'.$cacheHash;

        $cached = $this->cachePool->getItem($cacheKey);
        if ($cached->isHit()) {
            /** @var array{result: array{
             *     products: list<array{code: string, name: string, brand: ?string, nutriScore: ?string, novaGroup: ?int, imageUrl: ?string}>,
             *     total: int,
             *     page: int,
             *     limit: int,
             *     aggregations: array<string, int>,
             *     degraded: bool,
             *     degradationReason: ?string
             * }} $payload
             */
            $payload = $cached->get();

            return ProductSearchResult::fromArray($payload['result']);
        }

        try {
            $result = $this->fetchFromRemote($query);
            $this->storeCacheResult($cacheKey, $staleKey, $result);

            return $result;
        } catch (\Throwable $exception) {
            $this->logger->warning('Open Food Facts search failed; trying stale cache fallback.', [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
                'term' => $query->term,
                'filters' => $query->normalizedFilters(),
                'sortBy' => $query->sortBy,
            ]);

            $stale = $this->cachePool->getItem($staleKey);
            if ($stale->isHit()) {
                /** @var array{result: array{
                 *     products: list<array{code: string, name: string, brand: ?string, nutriScore: ?string, novaGroup: ?int, imageUrl: ?string}>,
                 *     total: int,
                 *     page: int,
                 *     limit: int,
                 *     aggregations: array<string, int>,
                 *     degraded: bool,
                 *     degradationReason: ?string
                 * }} $payload
                 */
                $payload = $stale->get();
                $fallback = ProductSearchResult::fromArray($payload['result']);

                return ProductSearchResult::degraded(
                    products: $fallback->products,
                    total: $fallback->total,
                    page: $fallback->page,
                    limit: $fallback->limit,
                    aggregations: $fallback->aggregations,
                    reason: 'stale_cache_fallback',
                );
            }

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

    private function fetchFromRemote(ProductSearchQuery $query): ProductSearchResult
    {
        $response = $this->httpClient->request('GET', rtrim($this->baseUri, '/').'/cgi/search.pl', [
            'query' => [
                'search_terms' => $query->term,
                'search_simple' => 1,
                'action' => 'process',
                'json' => 1,
                'page' => $query->page,
                'page_size' => $query->limit,
                'fields' => 'code,product_name,brands,nutriscore_grade,nova_group,image_front_small_url',
            ],
            'timeout' => $this->timeoutSeconds,
        ]);

        $statusCode = $response->getStatusCode();
        if ($statusCode >= 400) {
            throw new \RuntimeException(sprintf('Open Food Facts returned HTTP %d.', $statusCode));
        }

        try {
            /** @var array{products?: mixed, count?: mixed} $payload */
            $payload = $response->toArray();
        } catch (ExceptionInterface $exception) {
            throw new \RuntimeException('Unable to decode Open Food Facts payload.', 0, $exception);
        }

        if (!isset($payload['products']) || !is_array($payload['products'])) {
            throw new \RuntimeException('Malformed Open Food Facts payload: missing products.');
        }

        $products = array_values(array_filter(array_map(
            fn (mixed $row): ?ProductView => $this->mapProduct($row),
            $payload['products'],
        )));

        $products = $this->applyFilters($products, $query->normalizedFilters());
        $products = $this->applySort($products, $query->sortBy);
        $aggregations = $this->computeNutriScoreAggregation($products);

        $total = isset($payload['count']) && is_numeric($payload['count'])
            ? (int) $payload['count']
            : \count($products);

        return ProductSearchResult::success(
            products: $products,
            total: $total,
            page: $query->page,
            limit: $query->limit,
            aggregations: $aggregations,
        );
    }

    /**
     * @param mixed $row
     */
    private function mapProduct(mixed $row): ?ProductView
    {
        if (!is_array($row)) {
            return null;
        }

        $code = isset($row['code']) && is_scalar($row['code']) ? trim((string) $row['code']) : '';
        if ($code === '') {
            return null;
        }

        $name = isset($row['product_name']) && is_scalar($row['product_name'])
            ? trim((string) $row['product_name'])
            : '';
        if ($name === '') {
            $name = 'Unnamed product';
        }

        $brand = isset($row['brands']) && is_scalar($row['brands']) ? trim((string) $row['brands']) : null;
        $nutriScore = isset($row['nutriscore_grade']) && is_scalar($row['nutriscore_grade'])
            ? strtolower(trim((string) $row['nutriscore_grade']))
            : null;
        $novaGroup = isset($row['nova_group']) && is_numeric($row['nova_group']) ? (int) $row['nova_group'] : null;
        $imageUrl = isset($row['image_front_small_url']) && is_scalar($row['image_front_small_url'])
            ? trim((string) $row['image_front_small_url'])
            : null;

        return new ProductView(
            code: $code,
            name: $name,
            brand: $brand !== '' ? $brand : null,
            nutriScore: $nutriScore !== '' ? $nutriScore : null,
            novaGroup: $novaGroup,
            imageUrl: $imageUrl !== '' ? $imageUrl : null,
        );
    }

    /**
     * @param list<ProductView> $products
     * @param array<string, scalar> $filters
     *
     * @return list<ProductView>
     */
    private function applyFilters(array $products, array $filters): array
    {
        foreach ($filters as $filter => $value) {
            $normalizedValue = strtolower(trim((string) $value));
            if ($normalizedValue === '') {
                continue;
            }

            if ($filter === 'brand') {
                $products = array_values(array_filter(
                    $products,
                    static fn (ProductView $product): bool => $product->brand !== null
                        && str_contains(strtolower($product->brand), $normalizedValue),
                ));
                continue;
            }

            if ($filter === 'nutriscore') {
                $products = array_values(array_filter(
                    $products,
                    static fn (ProductView $product): bool => strtolower((string) $product->nutriScore) === $normalizedValue,
                ));
            }
        }

        return $products;
    }

    /**
     * @param list<ProductView> $products
     *
     * @return list<ProductView>
     */
    private function applySort(array $products, ?string $sortBy): array
    {
        if ($sortBy === null) {
            return $products;
        }

        usort($products, static function (ProductView $left, ProductView $right) use ($sortBy): int {
            return match ($sortBy) {
                'name_desc' => strcmp($right->name, $left->name),
                'nutriscore_asc' => strcmp((string) $left->nutriScore, (string) $right->nutriScore),
                'nutriscore_desc' => strcmp((string) $right->nutriScore, (string) $left->nutriScore),
                default => strcmp($left->name, $right->name),
            };
        });

        return $products;
    }

    /**
     * @param list<ProductView> $products
     *
     * @return array<string, int>
     */
    private function computeNutriScoreAggregation(array $products): array
    {
        $distribution = [
            'a' => 0,
            'b' => 0,
            'c' => 0,
            'd' => 0,
            'e' => 0,
            'unknown' => 0,
        ];

        foreach ($products as $product) {
            $grade = strtolower((string) $product->nutriScore);
            if (!array_key_exists($grade, $distribution)) {
                $distribution['unknown']++;
                continue;
            }

            $distribution[$grade]++;
        }

        return $distribution;
    }

    private function normalizedCachePayload(ProductSearchQuery $query): string
    {
        return json_encode([
            'term' => mb_strtolower(trim($query->term)),
            'page' => $query->page,
            'limit' => $query->limit,
            'filters' => $query->normalizedFilters(),
            'sortBy' => $query->sortBy,
        ], \JSON_THROW_ON_ERROR);
    }

    private function storeCacheResult(string $cacheKey, string $staleKey, ProductSearchResult $result): void
    {
        $payload = ['result' => $result->toArray()];

        $cacheItem = $this->cachePool->getItem($cacheKey);
        $cacheItem->set($payload);
        $cacheItem->expiresAfter($this->cacheTtlSeconds);
        $this->cachePool->save($cacheItem);

        $staleItem = $this->cachePool->getItem($staleKey);
        $staleItem->set($payload);
        $staleItem->expiresAfter($this->staleCacheTtlSeconds);
        $this->cachePool->save($staleItem);
    }
}
