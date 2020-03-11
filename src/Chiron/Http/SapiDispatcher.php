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
use Chiron\Boot\EnvironmentInterface;
use Nyholm\Psr7Server\ServerRequestCreatorInterface;

class SapiDispatcher implements DispatcherInterface
{
    /** @var ServerRequestInterface */
    private $request;
    /** @var Http */
    private $http;
    /** @var ResponseEmitter */
    private $emitter;

    // TODO : utiliser plutot un EmiterInterface au lieu de l'objet ResponseEmitter !!!!
    public function __construct(ServerRequestCreatorInterface $requestCreator, Http $http, ResponseEmitter $emitter)
    {
        $this->request = $requestCreator->fromGlobals();
        $this->http = $http;
        $this->emitter = $emitter;
    }

    /**
     * @inheritdoc
     */
    public function dispatch(): void
    {
        // TODO : il faudrait éventuellement mettre un try/catch autour du handle et du emit dans le cas ou le Middleware ErrorHandler n'est pas ajouté manuellement par l'utilisateur. Dans ce cas on afficherait l'erreur directement en HTML.  exemple :     https://github.com/spiral/framework/blob/master/src/Http/SapiDispatcher.php#L69
        $response = $this->http->handle($this->request);

        $this->emitter->emit($response);
    }

    /**
     * @inheritdoc
     */
    public function canDispatch(): bool
    {
        return php_sapi_name() !== 'cli';
    }
}
