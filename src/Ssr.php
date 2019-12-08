<?php

namespace Ssraas\LaravelSsraas;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\RequestException;
use Spatie\Ssr\Engines\Node;
use Spatie\Ssr\Exceptions\EngineError;
use Spatie\Ssr\Exceptions\ServerScriptDoesNotExist;

class Ssr
{
    protected $engine;

    protected $guzzle;

    protected $host = '';

    protected $app = '';

    protected $secret = '';

    protected $src = '';

    protected $context = [];

    protected $env = [];

    protected $enabled = true;

    protected $local = false;

    protected $fallback = '';

    protected $debug = false;

    protected $entryResolver;

    public function __construct(Guzzle $guzzle, Node $engine)
    {
        $this->guzzle = $guzzle;
        $this->engine = $engine;
    }

    /**
     * @param string|null $app
     * @param string|null $secret
     *
     * @return $this
     */
    public function auth($app, $secret)
    {
        $this->app = $app;
        $this->secret = $secret;

        return $this;
    }

    /**
     * @param string $enabled
     *
     * @return $this
     */
    public function host(string $host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @param bool $enabled
     *
     * @return $this
     */
    public function enabled(bool $enabled = true)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @param bool $enabled
     *
     * @return $this
     */
    public function local(bool $local = true)
    {
        $this->local = $local;

        return $this;
    }

    /**
     * @param bool $enabled
     *
     * @return $this
     */
    public function disabled(bool $disabled = true)
    {
        $this->enabled = ! $disabled;

        return $this;
    }

    /**
     * @param bool $debug
     *
     * @return $this
     */
    public function debug(bool $debug = true)
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * @param string $src
     *
     * @return $this
     */
    public function src(string $src)
    {
        $this->src = $src;

        return $this;
    }

    /**
     * @param string|array $key
     * @param mixed $value
     *
     * @return $this
     */
    public function context($context, $value = null)
    {
        if (! is_array($context)) {
            $context = [$context => $value];
        }

        foreach ($context as $key => $value) {
            $this->context[$key] = $value;
        }

        return $this;
    }

    /**
     * @param string|array $key
     * @param mixed $value
     *
     * @return $this
     */
    public function env($env, $value = null)
    {
        if (! is_array($env)) {
            $env = [$env => $value];
        }

        foreach ($env as $key => $value) {
            $this->env[$key] = $value;
        }

        return $this;
    }

    /**
     * @param string $fallback
     *
     * @return $this
     */
    public function fallback(string $fallback)
    {
        $this->fallback = $fallback;

        return $this;
    }

    /**
     * @param callable $resolver
     *
     * @return $this
     */
    public function resolveEntryWith(callable $entryResolver)
    {
        $this->entryResolver = $entryResolver;

        return $this;
    }

    /**
     *
     */
    public function render()
    {
        if (!$this->enabled) {
            return $this->fallback;
        }

        return $this->local
            ? $this->localRender()
            : $this->remoteRender();
    }

    /**
     *
     */
    private function remoteRender()
    {
        $contents = file_get_contents(public_path($this->src));

        try {
            $response = $this->guzzle
                ->request('POST', "{$this->host}/api/applications/{$this->app}/render", [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => "Bearer {$this->secret}",
                    ],
                    'multipart' => [
                        [
                            'name' => 'src',
                            'contents' => $contents,
                            'filename' => $this->src,
                        ],
                        [
                            'name' => 'checksum',
                            'contents' => crc32($contents),
                        ],
                        [
                            'name' => 'context',
                            'contents' => json_encode($this->context),
                        ],
                        [
                            'name' => 'env',
                            'contents' => json_encode($this->env),
                        ]
                    ]
                ]);

            $body = $response->getBody();

            $result = '';

            while (!$body->eof()) {
                $result .= $body->read(1024);
            }

            $decoded = json_decode($result, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $result = $decoded;
            }
        } catch (RequestException $e) {
            if ($this->debug) {
                throw $e;
            }

            $result = $this->fallback;
        }

        return $result;
    }

    private function localRender()
    {
        try {
            $serverScript = implode(';', [
                $this->dispatchScript(),
                $this->environmentScript(),
                $this->applicationScript(),
            ]);

            $result = $this->engine->run($serverScript);
        } catch (EngineError $exception) {
            if ($this->debug) {
                throw $exception->getException();
            }
            return $this->fallback;
        }

        $decoded = json_decode($result, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return $result;
    }

    protected function environmentScript(): string
    {
        $context = empty($this->context) ? '{}' : json_encode($this->context);

        $envAssignments = array_map(function ($value, $key) {
            return "process.env.{$key} = ".json_encode($value);
        }, $this->env, array_keys($this->env));

        return implode(';', [
            'var process = process || { env: {} }',
            implode(';', $envAssignments),
            "var context = {$context}",
        ]);
    }

    protected function dispatchScript() : string
    {
        return <<<JS
var dispatch = function (result) {
    return {$this->engine->getDispatchHandler()}(JSON.stringify(result))
}
JS;
    }

    protected function applicationScript() : string
    {
        $src = $this->entryResolver
            ? call_user_func($this->entryResolver, $this->src)
            : $this->src;

        if ( ! file_exists($src)) {
            throw ServerScriptDoesNotExist::atPath($src);
        }

        return file_get_contents($src);
    }
}
