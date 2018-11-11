<?php

declare(strict_types=1);

namespace Chiron\Routing;

interface RouteCollectionInterface
{
    /**
     * Add a route to the map.
     *
     * @param string          $path
     * @param callable|string $handler
     *
     * @return \Chiron\Routing\Route
     */
    public function map(string $path, $handler): Route;

    /**
     * Add GET route.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.1
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.3
     *
     * @param string          $pattern The route URI pattern
     * @param callable|string $handler The route callback routine
     *
     * @return \Chiron\Routing\Route
     */
    public function get(string $pattern, $handler): Route;

    /**
     * Add HEAD route.
     *
     * HEAD was added to HTTP/1.1 in RFC2616
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.2
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
     *
     * @param string          $pattern The route URI pattern
     * @param callable|string $handler The route callback routine
     *
     * @return \Chiron\Routing\Route
     */
    public function head(string $pattern, $handler): Route;

    /**
     * Add POST route.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.3
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.5
     *
     * @param string          $pattern The route URI pattern
     * @param callable|string $handler The route callback routine
     *
     * @return \Chiron\Routing\Route
     */
    public function post(string $pattern, $handler): Route;

    /**
     * Add PUT route.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.4
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.6
     *
     * @param string          $pattern The route URI pattern
     * @param callable|string $handler The route callback routine
     *
     * @return \Chiron\Routing\Route
     */
    public function put(string $pattern, $handler): Route;

    /**
     * Add DELETE route.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.5
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.7
     *
     * @param string          $pattern The route URI pattern
     * @param callable|string $handler The route callback routine
     *
     * @return \Chiron\Routing\Route
     */
    public function delete(string $pattern, $handler): Route;

    /**
     * Add OPTIONS route.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.7
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.2
     *
     * @param string          $pattern The route URI pattern
     * @param callable|string $handler The route callback routine
     *
     * @return \Chiron\Routing\Route
     */
    public function options(string $pattern, $handler): Route;

    /**
     * Add PATCH route.
     *
     * PATCH was added to HTTP/1.1 in RFC5789
     *
     * @see http://tools.ietf.org/html/rfc5789
     *
     * @param string          $pattern The route URI pattern
     * @param callable|string $handler The route callback routine
     *
     * @return \Chiron\Routing\Route
     */
    public function patch(string $pattern, $handler): Route;

    /**
     * Add route for any (official or unofficial) HTTP method.
     * use ->seAllowedMethods([]) with an empty array to support ALL the values (for custom method).
     *
     * @param string          $pattern The route URI pattern
     * @param callable|string $handler The route callback routine
     *
     * @return \Chiron\Routing\Route
     */
    public function any(string $pattern, $handler): Route;
}
