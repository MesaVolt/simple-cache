<?php

namespace Mesavolt\Tests;


use Mesavolt\SimpleCache;
use PHPUnit\Framework\TestCase;

class SimpleCacheTest extends TestCase
{
    /** @var SimpleCache */
    protected $cache;

    protected $hitCount;

    protected function setUp()
    {
        $this->cache = new SimpleCache(__DIR__.'/cache-dir');
        $this->cache->clear();
        $this->hitCount = 0;
    }

    protected function tearDown()
    {
        $this->cache->clear();
        $this->cache = null;
    }

    public function getValue()
    {
        $this->hitCount++;

        return "value$this->hitCount";
    }

    public function testGet()
    {
        $k = 'key';
        $callable = [$this, 'getValue'];
        $ttl = 2;

        $this->hitCount = 0;

        // first call should execute the callable that increments $this->count
        $value = $this->cache->get($k, $callable, $ttl);
        $this->assertEquals($value, 'value1');
        $this->assertEquals(1, $this->hitCount);

        // second call before TTL expires should *not* execute the callable,
        // so $this->count should still be 1 and value should be the same
        $value = $this->cache->get($k, $callable, $ttl);
        $this->assertEquals($value, 'value1');
        $this->assertEquals(1, $this->hitCount);

        // wait long enough to expire the cache
        sleep($ttl + 1);

        // third call is made after TTL has expired so the callable should be executed again,
        // so $this->count should have been incremented
        $value = $this->cache->get($k, $callable, $ttl);
        $this->assertEquals('value2', $value);
        $this->assertEquals(2, $this->hitCount);
    }

    public function testClear()
    {
        $k = 'k';
        $callable = [$this, 'getValue'];

        $value1 = $this->cache->get($k, $callable);

        $this->cache->clear();
        // this call is made after cache clear so the callable should be executed again,
        // son $this->count should have been incremented
        $value2 = $this->cache->get($k, $callable);

        $this->assertNotEquals($value1, $value2);
    }
}
