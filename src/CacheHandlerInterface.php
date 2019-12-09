<?php

namespace Ssraas\LaravelSsraas;

interface CacheHandlerInterface
{
    public function handle(string $src, array $context, array $env, callable $render);
}
