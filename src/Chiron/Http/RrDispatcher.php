<?php

declare(strict_types=1);

namespace Chiron\Http;

use Chiron\Boot\Environment;
use Spiral\RoadRunner\PSR7Client;

//https://github.com/spiral/framework/blob/master/src/Http/RrDispacher.php

class RrDispatcher implements DispatcherInterface
{
    /** @var Http */
    private $http;

    /** @var PSR7Client */
    private $client;

    /** @var Environment */
    private $env;

    public function __construct(Http $http, PSR7Client $client, Environment $env)
    {
        $this->http = $http;
        $this->client = $client;
        $this->env = $env;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(): void
    {
        while ($request = $this->client->acceptRequest()) {
            //try {
            $this->client->respond($this->http->handle($request));
            //} catch (\Throwable $e) {
            //    $this->handleException($client, $e);
            //} finally {
            //    $this->finalizer->finalize(false);
            //}
        }
    }

    /**
     * {@inheritdoc}
     */
    public function canDispatch(): bool
    {
        return php_sapi_name() === 'cli' && $this->env->get('RR') !== null;
    }
}
