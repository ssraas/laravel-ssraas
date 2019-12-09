<?php

namespace Ssraas\LaravelSsraas;

use Illuminate\Support\ServiceProvider;
use Spatie\Ssr\Engines\Node;
use Ssraas\LaravelSsraas\Resolvers\MixResolver;

class SsrServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/ssr.php' => config_path('ssr.php'),
        ], 'config');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/ssr.php', 'ssr');

        $this->app->singleton(Node::class, function () {
            return new Node(
                $this->app->config->get('ssr.node.node_path'),
                $this->app->config->get('ssr.node.temp_path')
            );
        });

        $this->app->resolving(
            Ssr::class,
            function (Ssr $ssr) {
                return $ssr
                    ->auth(
                        $this->app->config->get('services.ssraas.app'),
                        $this->app->config->get('services.ssraas.secret')
                    )
                    ->host($this->app->config->get('ssr.host'))
                    ->local($this->app->config->get('ssr.local'))
                    ->enabled($this->app->config->get('ssr.enabled'))
                    ->debug($this->app->config->get('ssr.debug'))
                    ->context('url', $this->app->request->getRequestUri())
                    ->context($this->app->config->get('ssr.context'))
                    ->env($this->app->config->get('ssr.env'))
                    ->cache($this->app->config->get('ssr.cache'))
                    ->resolveEntryWith(new MixResolver($this->app->config->get('ssr.mix')));;
            }
        );

        $this->app->alias(Ssr::class, 'ssr');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return ['ssr'];
    }
}
