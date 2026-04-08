<?php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Provides caching utilities for the application
 * Uses Redis in production, filesystem cache in development
 */
class CacheService
{
    public function __construct(private CacheInterface $cache) {}

    /**
     * Get or compute a cached value
     */
    public function getOrCompute(string $key, callable $callback, int $ttl = 3600): mixed
    {
        return $this->cache->get($key, function (ItemInterface $item) use ($callback, $ttl) {
            $item->expiresAfter($ttl);
            return $callback();
        });
    }

    /**
     * Store a value in cache
     */
    public function set(string $key, mixed $value, int $ttl = 3600): void
    {
        $item = $this->cache->getItem($key);
        $item->set($value)->expiresAfter($ttl);
        $this->cache->save($item);
    }

    /**
     * Get a cached value
     */
    public function get(string $key): mixed
    {
        $item = $this->cache->getItem($key);
        return $item->isHit() ? $item->get() : null;
    }

    /**
     * Delete a cached value
     */
    public function delete(string $key): void
    {
        $this->cache->deleteItem($key);
    }

    /**
     * Clear all cache
     */
    public function clear(): void
    {
        $this->cache->clear();
    }

    /**
     * Cache key for products
     */
    public static function productCacheKey(int $id): string
    {
        return "product_{$id}";
    }

    /**
     * Cache key for categories
     */
    public static function categoryCacheKey(int $id): string
    {
        return "category_{$id}";
    }

    /**
     * Cache key for search results
     */
    public static function searchCacheKey(string $query, int $page = 1): string
    {
        return "search_" . md5($query . "_page_{$page}");
    }

    /**
     * Cache key for product search
     */
    public static function productSearchCacheKey(array $filters, int $page = 1): string
    {
        return "product_search_" . md5(json_encode($filters) . "_page_{$page}");
    }
}
