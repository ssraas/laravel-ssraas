<?php

namespace Ssraas\LaravelSsraas;

use Illuminate\Cache\Repository as Cache;

class CacheHandler implements CacheHandlerInterface
{
    protected $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function handle(string $src, array $context, array $env, callable $render)
    {
        $filesize = filesize(public_path($src));

        $key = md5($filesize.json_encode($context).json_encode($env));

        return $this->cache->remember("ssr:{$key}", now()->addHour(24), function () use ($render) {
            return $render();
        });
    }
}
