<?php

declare(strict_types=1);

namespace Chiron;

use Chiron\Config\ConfigInterface;
use Chiron\Config\ConfigManager;
use Chiron\Container\Container;
use Chiron\Http\Emitter\ResponseEmitter;
use Chiron\Http\Emitter\SapiEmitter;
use Chiron\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Chiron\Boot\DirectoriesInterface;
use Chiron\Boot\Directories;
use Chiron\Boot\EnvironmentInterface;
use Chiron\Boot\Environment;
use Nyholm\Psr7Server\ServerRequestCreatorInterface;

class Application
{
    /** @var ServerRequestInterface */
    private $request;
    /** @var RouterInterface */
    private $router;
    /** @var ResponseEmitter */
    private $emitter;

// TODO : utiliser plutot un EmiterInterface au lieu de l'objet ResponseEmitter !!!!
    public function __construct(ServerRequestCreatorInterface $requestCreator, RouterInterface $router, ResponseEmitter $emitter)
    {
        $this->request = $requestCreator->fromGlobals();
        $this->router = $router;
        $this->emitter = $emitter;
    }

    /*******************************************************************************
     * Middleware Stack
     ******************************************************************************/

    /**
     * Add a middleware.
     *
     * Proxies to the Router::middleware() method.
     */
    public function middleware($middleware): self
    {
        $this->router->middleware($middleware);

        return $this;
    }

    /*******************************************************************************
     * Run App
     ******************************************************************************/

    public function run(): void
    {
        $response = $this->router->handle($this->request);

        $this->emitter->emit($response);
    }
}
