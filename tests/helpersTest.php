<?php

namespace Ssraas\LaravelSsraas\Tests;

use Orchestra\Testbench\TestCase;
use Ssraas\LaravelSsraas\Ssr;
use Ssraas\LaravelSsraas\SsrServiceProvider;

class helpersTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            SsrServiceProvider::class
        ];
    }

    public function test_returns_instance_of_ssr()
    {
        $this->assertInstanceOf(Ssr::class, ssr());
    }

    public function test_returns_instance_with_src_set()
    {
        $this->assertEquals(
            'test.js',
            get_protected_property(ssr('test.js'), 'src')
        );
    }
}
