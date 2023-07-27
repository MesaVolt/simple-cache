<?php

namespace Mesavolt\Tests;


use Mesavolt\SimpleCache;
use PHPUnit\Framework\TestCase;

class SimpleCacheTest extends TestCase
{
    protected SimpleCache $cache;

    protected int $hitCount;

    protected function setUp(): void
    {
        $this->cache = new SimpleCache(__DIR__.'/cache-dir');
        $this->cache->clear();
        $this->hitCount = 0;
    }

    protected function tearDown(): void
    {
        $this->cache->clear();
    }

    public function getValue(): string
    {
        $this->hitCount++;

        return "value$this->hitCount";
    }

    public function testGet(): void
    {
        $k = 'key';
        $callable = [$this, 'getValue'];
        $ttl = 2;

        $this->hitCount = 0;

        // first call should execute the callable that increments $this->count
        $value = $this->cache->get($k, $callable, $ttl);
        self::assertEquals('value1', $value);
        self::assertEquals(1, $this->hitCount);

        // second call before TTL expires should *not* execute the callable,
        // so $this->count should still be 1 and value should be the same
        $value = $this->cache->get($k, $callable, $ttl);
        self::assertEquals('value1', $value);
        self::assertEquals(1, $this->hitCount);

        // wait long enough to expire the cache
        sleep($ttl + 1);

        // third call is made after TTL has expired so the callable should be executed again,
        // so $this->count should have been incremented
        $value = $this->cache->get($k, $callable, $ttl);
        self::assertEquals('value2', $value);
        self::assertEquals(2, $this->hitCount);
    }

    public function testClear(): void
    {
        $k = 'k';
        $callable = [$this, 'getValue'];

        $value1 = $this->cache->get($k, $callable);

        $this->cache->clear();
        // this call is made after cache clear so the callable should be executed again,
        // son $this->count should have been incremented
        $value2 = $this->cache->get($k, $callable);

        self::assertNotEquals($value1, $value2);
    }

    public function testRead(): void
    {
        $this->cache->get('key', static fn() => 'testRead', 10);
        self::assertEquals('testRead', $this->cache->read('key'));

        self::assertEquals(null, $this->cache->read('absent'));
    }

    public function testSet(): void
    {
        $this->cache->set('key', 'testSet', 10);
        self::assertEquals('testSet', $this->cache->read('key'));
    }

    public function testDelete(): void
    {
        $this->cache->set('key', 'testDelete', SimpleCache::TTL_1_YEAR);
        self::assertEquals('testDelete', $this->cache->read('key'));

        $this->cache->delete('key');
        self::assertNull($this->cache->read('key'));
    }
}
