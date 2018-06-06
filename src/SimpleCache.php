<?php

namespace Mesavolt;


use Symfony\Component\Cache\Simple\AbstractCache;
use Symfony\Component\Cache\Simple\FilesystemCache;

class SimpleCache
{
    public const TTL_5_MINUTES  = 300;
    public const TTL_30_MINUTES = 1800;
    public const TTL_1_DAY      = 86400;
    public const TTL_1_YEAR     = 31556926;

    private const DEFAULT_CACHE_TTL = self::TTL_5_MINUTES; // 5 minutes

    /** @var AbstractCache */
    private $cache;

    public function __construct(string $cacheDir, string $namespace = '')
    {
        $this->cache = new FilesystemCache($namespace, self::DEFAULT_CACHE_TTL, $cacheDir);
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
        // Cached value is available: just return it
        if ($this->cache->has($key)) {
            return json_decode($this->cache->get($key), true);
        }

        // Compute value
        $value = json_encode($callable());
        $this->cache->set($key, $value, $ttl);

        return json_decode($value, true);
    }

    public function clear(): bool
    {
        return $this->cache->clear();
    }
}
