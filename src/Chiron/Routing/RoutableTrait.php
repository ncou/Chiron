<?php

namespace Chiron\Routing;

// TODO : ajouter le support pour les méthodes TRACE et CONNECT ????

trait RoutableTrait
{
    /**
     * Add GET route.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.1
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.3
     *
     * @param string                                    $pattern    The route URI pattern
     * @param callable|string                           $handler    The route callback routine
     * @param string|array|callable|MiddlewareInterface $middleware
     *
     * @return \Chiron\Routing\Route
     */
    public function get(string $pattern, $handler, $middlewares = null): Route
    {
        return $this->map($pattern, $handler, $middlewares)->method('GET');
    }

    /**
     * Add HEAD route.
     *
     * HEAD was added to HTTP/1.1 in RFC2616
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.2
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
     *
     * @param string                                    $pattern    The route URI pattern
     * @param callable|string                           $handler    The route callback routine
     * @param string|array|callable|MiddlewareInterface $middleware
     *
     * @return \Chiron\Routing\Route
     */
    public function head(string $pattern, $handler, $middlewares = null): Route
    {
        return $this->map($pattern, $handler, $middlewares)->method('HEAD');
    }

    /**
     * Add POST route.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.3
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.5
     *
     * @param string                                    $pattern    The route URI pattern
     * @param callable|string                           $handler    The route callback routine
     * @param string|array|callable|MiddlewareInterface $middleware
     *
     * @return \Chiron\Routing\Route
     */
    public function post(string $pattern, $handler, $middlewares = null): Route
    {
        return $this->map($pattern, $handler, $middlewares)->method('POST');
    }

    /**
     * Add PUT route.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.4
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.6
     *
     * @param string                                    $pattern    The route URI pattern
     * @param callable|string                           $handler    The route callback routine
     * @param string|array|callable|MiddlewareInterface $middleware
     *
     * @return \Chiron\Routing\Route
     */
    public function put(string $pattern, $handler, $middlewares = null): Route
    {
        return $this->map($pattern, $handler, $middlewares)->method('PUT');
    }

    /**
     * Add DELETE route.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.5
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.7
     *
     * @param string                                    $pattern    The route URI pattern
     * @param callable|string                           $callable   The route callback routine
     * @param string|array|callable|MiddlewareInterface $middleware
     *
     * @return \Chiron\Routing\Route
     */
    public function delete(string $pattern, $handler, $middlewares = null): Route
    {
        return $this->map($pattern, $handler, $middlewares)->method('DELETE');
    }

    /**
     * Add OPTIONS route.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.7
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.2
     *
     * @param string                                    $pattern    The route URI pattern
     * @param callable|string                           $handler    The route callback routine
     * @param string|array|callable|MiddlewareInterface $middleware
     *
     * @return \Chiron\Routing\Route
     */
    public function options(string $pattern, $handler, $middlewares = null): Route
    {
        return $this->map($pattern, $handler, $middlewares)->method('OPTIONS');
    }

    /**
     * Add PATCH route.
     *
     * PATCH was added to HTTP/1.1 in RFC5789
     *
     * @see http://tools.ietf.org/html/rfc5789
     *
     * @param string                                    $pattern    The route URI pattern
     * @param callable|string                           $handler    The route callback routine
     * @param string|array|callable|MiddlewareInterface $middleware
     *
     * @return \Chiron\Routing\Route
     */
    public function patch(string $pattern, $handler, $middlewares = null): Route
    {
        return $this->map($pattern, $handler, $middlewares)->method('PATCH');
    }

    /**
     * Add PURGE route.
     *
     * PURGE is not an official method, and there is no RFC for the moment.
     *
     * @param string                                    $pattern    The route URI pattern
     * @param callable|string                           $handler    The route callback routine
     * @param string|array|callable|MiddlewareInterface $middleware
     *
     * @return \Chiron\Routing\Route
     */
    /*
    public function purge(string $pattern, $handler, $middlewares = null): Route
    {
        return $this->map($pattern, $handler, $middlewares)->method('PURGE');
    }*/

    /**
     * Add route for any (official or unofficial) HTTP method.
     * use ->seAllowedMethods([]) with an empty array to support ALL the values (for custom method).
     *
     * @param string                                    $pattern    The route URI pattern
     * @param callable|string                           $handler    The route callback routine
     * @param string|array|callable|MiddlewareInterface $middleware
     *
     * @return \Chiron\Routing\Route
     */
    public function any(string $pattern, $handler, $middlewares = null): Route
    {
        //return $this->map($pattern, $handler, $middlewares)->setAllowedMethods(['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'PURGE', 'DELETE', 'OPTIONS']);
        return $this->map($pattern, $handler, $middlewares)->setAllowedMethods(['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']);
    }
}
