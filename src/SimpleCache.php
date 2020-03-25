<?php

namespace Mesavolt;


use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\CacheInterface;

class SimpleCache
{
    public const TTL_5_MINUTES  = 300;
    public const TTL_30_MINUTES = 1800;
    public const TTL_1_DAY      = 86400;
    public const TTL_1_YEAR     = 31556926;

    private const DEFAULT_CACHE_TTL = self::TTL_5_MINUTES; // 5 minutes

    /** @var CacheInterface */
    private $cache;

    public function __construct(string $cacheDir, string $namespace = '')
    {
        $this->cache = new FilesystemAdapter($namespace, self::DEFAULT_CACHE_TTL, $cacheDir);
    }

    /**
     * Returns a cached value if available, or computes it by calling $callable argument and stores it in cache.
     *
     * Usage:
     *
     * ```
     * SimpleCacheService::get('my-unique-key', function() {
     *     return pi();
     * });
     * ```
     *
     * Note: if a cache hit could not be achieved, we still json_encode + json_decode the value.
     * That's on purpose, to avoid differences between cache hit & cache misses.
     */
    public function get(string $key, callable $callable, int $ttl = null)
    {
        /** @var CacheItemInterface $item */
        $item = $this->cache->getItem($key);

        // Cached value is available: just return it
        if ($item->isHit()) {
            return json_decode($item->get(), true);
        }

        // Compute value
        $value = json_encode($callable());

        // Store value
        $item->set($value);
        $item->expiresAfter($ttl);
        $this->cache->save($item);

        // return value
        return json_decode($value, true);
    }

    public function clear(): bool
    {
        return $this->cache->clear();
    }
}
