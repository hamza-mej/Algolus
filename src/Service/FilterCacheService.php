<?php

namespace App\Service;

use App\Repository\CategoryRepository;
use App\Repository\ColorRepository;
use App\Repository\SizeRepository;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class FilterCacheService
{
    private const CACHE_TTL = 3600; // 1 hour

    public function __construct(
        private CacheInterface $cache,
        private CategoryRepository $categoryRepository,
        private ColorRepository $colorRepository,
        private SizeRepository $sizeRepository,
    ) {}

    /**
     * Get all categories (cached)
     */
    public function getCategories(): array
    {
        return $this->cache->get('shop_categories', function (ItemInterface $item) {
            $item->expiresAfter(self::CACHE_TTL);
            return $this->categoryRepository->findAll();
        });
    }

    /**
     * Get all colors (cached)
     */
    public function getColors(): array
    {
        return $this->cache->get('shop_colors', function (ItemInterface $item) {
            $item->expiresAfter(self::CACHE_TTL);
            return $this->colorRepository->findAll();
        });
    }

    /**
     * Get all sizes (cached)
     */
    public function getSizes(): array
    {
        return $this->cache->get('shop_sizes', function (ItemInterface $item) {
            $item->expiresAfter(self::CACHE_TTL);
            return $this->sizeRepository->findAll();
        });
    }

    /**
     * Clear all filter caches
     */
    public function clearFilterCaches(): void
    {
        $this->cache->delete('shop_categories');
        $this->cache->delete('shop_colors');
        $this->cache->delete('shop_sizes');
    }
}
