<?php

declare(strict_types=1);

//https://github.com/thephpleague/route/blob/master/src/Middleware/MiddlewareAwareTrait.php

namespace Chiron\Routing;

use Psr\Http\Server\MiddlewareInterface;

trait MiddlewareAwareTrait
{
    /**
     * @var \Psr\Http\Server\MiddlewareInterface[]
     */
    protected $middlewares = [];

    /**
     * {@inheritdoc}
     */
    public function middleware($middleware): MiddlewareAwareInterface
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * Add a middleware to the end of the stack.
     *
     * @param string|callable|MiddlewareInterface or an array of such arguments $middlewares
     *
     * @return $this (for chaining)
     */
    // TODO : gérer aussi les tableaux de middleware, ainsi que les tableaux de tableaux de middlewares
    /*
    public function middleware($middlewares)
    {
        if (! is_array($middlewares)) {
            $middlewares = [$middlewares];
        }

        foreach ($middlewares as $middleware) {
            //$this->requestHandler->prepend($this->prepareMiddleware($middleware));
            array_push($this->middlewares, $middleware);
        }

        return $this;
    }*/

    /**
     * {@inheritdoc}
     */
    public function middlewares(array $middlewares): MiddlewareAwareInterface
    {
        foreach ($middlewares as $middleware) {
            $this->middleware($middleware);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function prependMiddleware($middleware): MiddlewareAwareInterface
    {
        array_unshift($this->middlewares, $middleware);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    /*
    public function shiftMiddleware() : MiddlewareInterface
    {
        return array_shift($this->middleware);
    }*/

    /**
     * {@inheritdoc}
     */
    public function getMiddlewareStack(): array
    {
        return $this->middlewares;
    }

    /*
     * Add middleware to the beginning of the stack
     *
     * @param MiddlewareInterface $middleware Middleware function
     */
    /*
    public function prepend(MiddlewareInterface $middleware): MiddlewareAwareInterface
    {
        array_unshift($this->middlewares, $middleware);

        return $this;
    }*/

    /*
     * Add middleware to the end of the stack
     *
     * @param MiddlewareInterface $middleware Middleware function
     */
    /*
    public function append(MiddlewareInterface $middleware): MiddlewareAwareInterface
    {
        array_push($this->middlewares, $middleware);

        return $this;
    }*/

    /*
     * Add middleware to the end of the stack
     */
    /*
    public function append(MiddlewareInterface ...$middleware): MiddlewareAwareInterface
    {
        array_push($this->middlewares, ...$middleware);
        return $this;
    }*/
    /*
     * Add middleware to the beginning of the stack
     */
    /*
    public function prepend(MiddlewareInterface ...$middleware): MiddlewareAwareInterface
    {
        array_unshift($this->middlewares, ...$middleware);
        return $this;
    }*/
}
