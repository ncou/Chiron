<?php

declare(strict_types=1);

namespace Chiron\Stack;

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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use UnexpectedValueException;

// TODO : renommer cette classe en RequestHandlerStack
class RequestHandlerStack implements RequestHandlerInterface
{
    /**
     * @var array MiddlewareInterface[]|callable[]|string[]
     */
    private $middlewares; // TODO : utiliser un "new SplQueue()" au lieu d'un array ???? https://github.com/zendframework/zend-stratigility/blob/master/src/MiddlewarePipe.php#L44 // TODO : utiliser un SplStack à la place ? les fonctions push/unshift fonctionnent !!!!
    /**
     * @var callable
     */
    private $fallbackHandler;
    /**
     * @var int
     */
    private $index = 0;

    /**
     * @param array    $middlewares
     * @param callable $fallbackHandler
     */
    public function __construct(RequestHandlerInterface $fallbackHandler, array $middlewares = [])
    {
        $this->fallbackHandler = $fallbackHandler;
        $this->middlewares = $middlewares;
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
        //if (!array_key_exists($this->index, $this->middlewares)) {}

        // execute the middlewares
        switch (true) {
            // end of the stack, execute the default RequestHandler for creating the response
            //case empty($middleware):
            case is_null($middleware):
                //$result = call_user_func($this->fallbackHandler, $request);
                $result = $this->fallbackHandler->handle($request);
                break;
            case $middleware instanceof MiddlewareInterface:
                $result = $middleware->process($request, $this->nextHandler());
                break;
            default:
                throw new InvalidArgumentException(sprintf('No valid middleware provided (%s)', is_object($middleware) ? get_class($middleware) : gettype($middleware)));
        }

        // middleware MUST return a ResponseInterface object
        if (! $result instanceof ResponseInterface) {
            throw new UnexpectedValueException('Middleware must return instance of (\Psr\Http\Message\ResponseInterface)');
        }

        return $result;
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
     * Create a middleware from a closure.
     */
    /*
    private static function createMiddlewareFromClosure(Closure $handler): MiddlewareInterface
    {
        return new class($handler) implements MiddlewareInterface {
            private $handler;
            public function __construct(Closure $handler)
            {
                $this->handler = $handler;
            }
            public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
            {
                return call_user_func($this->handler, $request, $next);
            }
        };
    }*/

    /**
     * Insert middlewares to the next position.
     *
     * @param array $middlewares
     * @param null  $index
     *
     * @return $this
     */
    //https://github.com/swoft-cloud/swoft-framework/blob/e04d1293cc5b4a8a532fce9bc20c6eb6f0f8abc8/src/Core/RequestHandler.php#L88
    /*
    public function insertMiddlewares(array $middlewares, $index = null)
    {
        is_null($index) && $index = $this->index;
        $chunkArray = array_chunk($this->middlewares, $index);
        $after = [];
        $before = $chunkArray[0];
        if (isset($chunkArray[1])) {
            $after = $chunkArray[1];
        }
        $middlewares = array_merge((array)$before, $middlewares, (array)$after);
        $this->middlewares = $middlewares;
        return $this;
    }*/

    /**
     * Remove a middleware by instance or name from the stack.
     *
     * @param callable|string $remove Middleware to remove by instance or name.
     */
    // TODO : on a vraiment besoin d'une méthode remove ?????
    /*
    public function remove($remove)
    {
        $this->middlewares = array_values(array_filter(
            $this->middlewares,
            function ($middleware) use ($remove) {
                return $middleware != $remove;
            }
        ));
    }*/

    /*
    //https://github.com/idealo/php-middleware-stack/blob/use-new-psr15-interfaces/src/Stack.php#L28
        private function withoutMiddleware(MiddlewareInterface $middleware): RequestHandlerInterface
        {
            return new self(
                $this->defaultResponse,
                ...array_filter(
                    $this->middlewares,
                    function ($m) use ($middleware) {
                        return $middleware !== $m;
                    }
                )
            );
        }
    */

    /**
     * Unshift a middleware to the bottom of the stack.
     *
     * @param MiddlewareInterface $middleware Middleware function
     */
    public function append(MiddlewareInterface $middleware)
    {
        array_unshift($this->middlewares, $middleware);
    }

    /**
     * Push a middleware to the top of the stack.
     *
     * @param MiddlewareInterface $middleware Middleware function
     */
    public function prepend(MiddlewareInterface $middleware)
    {
        array_push($this->middlewares, $middleware);
    }
}
