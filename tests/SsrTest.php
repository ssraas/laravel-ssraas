<?php

namespace Ssraas\LaravelSsraas\Tests;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Illuminate\Cache\Repository as Cache;
use Orchestra\Testbench\TestCase;
use Spatie\Ssr\Engines\Node;
use Ssraas\LaravelSsraas\CacheHandler;
use Ssraas\LaravelSsraas\Ssr;
use Ssraas\LaravelSsraas\SsrServiceProvider;

class SsrTest extends TestCase
{
    public function setUp() : void
    {
        parent::setUp();

        $this->app->instance('path.public', __DIR__.'/public');
    }

    protected function getPackageProviders($app)
    {
        return [
            SsrServiceProvider::class
        ];
    }

    public function test_guzzle()
    {
        $mock = new MockHandler([
            new Response(200, [], '<div>test</div>')
        ]);

        $handlerStack = HandlerStack::create($mock);

        $guzzle = new Guzzle(['handler' => $handlerStack]);

        $node = new Node('', '');

        $ssr = new Ssr($guzzle, $node);

        $result = $ssr->src('js/app-server.js')->render();

        $this->assertEquals('<div>test</div>', $result);
    }

    public function test_cache()
    {
        $ssr = new Ssr(app(Guzzle::class), new Node('', ''));

        $this->mock(Cache::class, function ($mock) {
            $mock->shouldReceive('remember')
                ->once()
                ->withArgs(function ($key, $expires, $callback) {
                    return $key === 'ssr:18bb05d52d9f53c72179eadb9141fc8c';
                })
                ->andReturn('<div>test</div>');
        });

        $result = $ssr->src('js/app-server.js')
            ->cache(CacheHandler::class)
            ->render();

        $this->assertEquals('<div>test</div>', $result);
    }
}
