<?php

declare(strict_types=1);

namespace Chiron\Http;

use Chiron\Router\Traits\MiddlewareAwareInterface;
use Chiron\Router\Traits\MiddlewareAwareTrait;
use Chiron\Router\Traits\RouteCollectionInterface;
use Chiron\Router\Traits\RouteCollectionTrait;
use Chiron\Pipe\PipelineBuilder;
use Chiron\Router\RouterInterface;
use Chiron\Router\Route;
use Chiron\Router\Method;
use Chiron\Router\RequestHandler;
use Chiron\Router\RouteCollectorInterface;
use Chiron\Router\RouteGroup;
use Chiron\Router\MatchingResult;
use Chiron\Router\RoutingHandler;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Psr\Http\Server\RequestHandlerInterface;


use Chiron\Config\ConfigInterface;
use Chiron\Config\ConfigManager;
use Chiron\Container\Container;
use Chiron\Http\Emitter\ResponseEmitter;
use Chiron\Http\Emitter\SapiEmitter;
use Psr\Log\LoggerInterface;
use Chiron\Boot\DirectoriesInterface;
use Chiron\Boot\Directories;
use Chiron\Boot\Environment;
use Chiron\Boot\EnvironmentInterface;
use Nyholm\Psr7Server\ServerRequestCreatorInterface;
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
     * @inheritdoc
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
     * @inheritdoc
     */
    public function canDispatch(): bool
    {
        return php_sapi_name() === 'cli' && $this->env->get('RR') !== null;
    }
}
