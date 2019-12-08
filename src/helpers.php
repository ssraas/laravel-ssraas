<?php

function ssr(string $src = null)
{
    if (func_num_args() === 0) {
        return app('ssr');

    }

    return app('ssr')->src($src);
}
