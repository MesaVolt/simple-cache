# SimpleCache


[![Latest Stable Version](https://poser.pugx.org/mesavolt/simple-cache/v/stable)](https://packagist.org/packages/mesavolt/simple-cache)
[![Build Status](https://travis-ci.org/MesaVolt/simple-cache.svg)](https://travis-ci.org/MesaVolt/simple-cache)
[![Coverage Status](https://coveralls.io/repos/github/MesaVolt/simple-cache/badge.svg)](https://coveralls.io/github/MesaVolt/simple-cache)
[![License](https://poser.pugx.org/mesavolt/simple-cache/license)](https://packagist.org/packages/mesavolt/simple-cache)


Simple cache system based on Symfony's Cache component.
Will always return a value, fresh or from cache, depending on TTL.
# Installation

```bash
composer require mesavolt/simple-cache
```

# Usage

```php
<?php

use Mesavolt\SimpleCache;

$cache = new SimpleCache(sys_get_temp_dir());
$ttl = 10; // values expire after 10 seconds

// use the cache for the 1st, the callable is executed, a fresh value is cached and returned
$value1 = $cache->get('key', function () {
    return time();
}, $ttl);

// use the cache again before TTL has passed.
// the callable is *not* executed again, the previously cached value is returned
$value2 = $cache->get('key', function () {
    return time();
}, $ttl);

assert($value2 === $value1); // true because TTL hasn't expired

// sleep 20 seconds, this is longer that our TTL of 10 seconds
sleep(20);

// use the cache for the 3rd time, TTL has expired so a new fresh value is cached and returned
$value3 = $cache->get('key', function () {
    return time();
});

assert($value3 !== $value1); // true because `sleep(20)` expired the TTL
                             // so the callable has been executed
                             // and a fresh value has been returned (and cached)
```
