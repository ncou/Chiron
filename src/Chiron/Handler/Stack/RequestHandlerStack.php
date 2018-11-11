<?php

declare(strict_types=1);

namespace Chiron\Handler\Stack;

//https://github.com/reactphp/http/blob/master/src/Io/MiddlewareRunner.php

//https://github.com/cakephp/cakephp/blob/master/src/Http/MiddlewareQueue.php
//https://github.com/cakephp/cakephp/blob/master/src/Http/Runner.php

//https://github.com/narrowspark/framework/blob/master/src/Viserio/Component/Pipeline/Pipeline.php
//https://github.com/illuminate/pipeline/blob/master/Pipeline.php
//https://github.com/mpociot/pipeline/blob/master/src/Pipeline.php

//https://github.com/emonkak/php-http-middleware/blob/master/src/Internal/Pipeline.php

//https://github.com/relayphp/Relay.Relay/blob/2.x/src/Runner.php
//https://github.com/cakephp/cakephp/blob/master/src/Http/Runner.php

//https://github.com/guzzle/guzzle/blob/master/src/HandlerStack.php

//https://github.com/reactphp/http/blob/master/src/Io/MiddlewareRunner.php

//https://github.com/middlewares/utils

//https://github.com/zendframework/zend-stratigility/blob/master/src/MiddlewarePipe.php

// TODO : renommer en RequestHandlerRunner ???? https://github.com/zendframework/zend-httphandlerrunner/blob/master/src/RequestHandlerRunner.php

// TODO : pour la documentation avec les schémas de l'oignon il faudra utiliser ce site : https://book.cakephp.org/3.0/fr/controllers/middleware.html

//namespace Equip\Dispatch;

//***********************
// TODO : regarder comment guzzle gérer les middlewares via la méthode push et create : https://github.com/guzzle/guzzle/blob/master/src/HandlerStack.php
// utilisation : https://github.com/GrahamCampbell/Guzzle-Factory/blob/master/src/GuzzleFactory.php#L87
//***********************

//TODO : regarder ici comment c'est fait : https://github.com/madewithlove/jenga

//https://github.com/swoft-cloud/swoft-framework/blob/master/src/Core/RequestHandler.php

// Inspirations :
//https://github.com/equip/dispatch/blob/master/src/Handler.php
//https://github.com/oscarotero/middleland/blob/master/src/Dispatcher.php
//https://github.com/moon-php/http-middleware/blob/master/src/Delegate.php
//https://github.com/mindplay-dk/middleman/blob/master/src/Dispatcher.php
//https://github.com/northwoods/broker/blob/master/src/Broker.php

//https://github.com/koolkode/http-komponent/blob/master/src/MiddlewareChain.php

//https://github.com/idealo/php-middleware-stack/blob/use-new-psr15-interfaces/src/Stack.php

// TODO : prendre exemple ici pour gérer la méthode offsetSet sur une stack ???? https://github.com/zendframework/zend-httphandlerrunner/blob/master/src/Emitter/EmitterStack.php#L55

use InvalidArgumentException;
use Chiron\Handler\Stack\Decorator\CallableMiddlewareDecorator;
use Chiron\Handler\Stack\Decorator\LazyLoadingMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Container\ContainerInterface;
use UnexpectedValueException;
use OutOfBoundsException;

// TODO : renommer cette classe en RequestHandlerStack
class RequestHandlerStack implements RequestHandlerInterface
{
    /**
     * Dependency injection container.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array MiddlewareInterface[]
     */
    private $middlewares = [];

    /**
     * @var int
     */
    private $index = 0;


    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param string|callable|MiddlewareInterface $middlewares
     */
    public function seed(array $middlewares = []): self
    {
        foreach ($middlewares as $middleware) {
            //array_push($this->middlewares, $this->prepareMiddleware($middleware));
            $this->middlewares[] = $this->prepareMiddleware($middleware);
        }

        return $this;
    }

    /**
     * Process the request (using the current middlewares) and return a response.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = $this->middlewares[$this->index] ?? null;

        // TODO : je pense qu'on peut aussi faire un test via un is_empty() => https://github.com/equip/dispatch/blob/master/src/Handler.php#L38
        if (is_null($middleware)) {
            throw new OutOfBoundsException('Reached end of middleware stack. Does your controller return a response ?');
        }

        return $middleware->process($request, $this->nextHandler());
    }

    /**
     * Get a handler pointing to the next middleware.
     *
     * @return static
     */
    private function nextHandler(): RequestHandlerInterface
    {
        $copy = clone $this;
        $copy->index++;

        return $copy;
    }

    /**
     * Decorate the middleware if necessary.
     *
     * @param string|callable|MiddlewareInterface $middleware
     *
     * @return MiddlewareInterface
     */
    private function prepareMiddleware($middleware): MiddlewareInterface
    {
        if ($middleware instanceof MiddlewareInterface) {
            return $middleware;
        } elseif (is_callable($middleware)) {
            return new CallableMiddlewareDecorator($middleware);
        } elseif (is_string($middleware) && $middleware !== '') { // TODO : vérifier l'utilité du chaine vide !!!!
            return new LazyLoadingMiddleware($middleware, $this->container);
        } else {
            throw new InvalidArgumentException(sprintf(
                'Middleware "%s" is neither a string service name, a PHP callable, or a %s instance',
                is_object($middleware) ? get_class($middleware) : gettype($middleware),
                MiddlewareInterface::class
            ));
        }
    }

}
