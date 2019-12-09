<?php

/**
 * @param string|null $src
 *
 * @return Ssraas\LaravelSsraas\Ssr
 */
function ssr(string $src = null)
{
    $ssr = app('ssr');

    return func_num_args() === 0 ? $ssr : $ssr->src($src);
}
