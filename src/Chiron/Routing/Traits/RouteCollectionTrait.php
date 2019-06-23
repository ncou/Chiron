<?php

declare(strict_types=1);

namespace Chiron\Routing\Traits;

use Chiron\Routing\Route;
use Chiron\Routing\RouteGroup;

trait RouteCollectionTrait
{
    /**
     * Group a bunch of routes.
     *
     * @param string   $prefix
     * @param callable $group
     *
     * @return \Chiron\Routing\RouteGroup
     */
    abstract public function group(string $prefix, callable $group): RouteGroup;

    /**
     * Add a route to the map.
     *
     * @param string          $path
     * @param callable|string $handler
     *
     * @return \Chiron\Routing\Route
     */
    abstract public function map(string $path, $handler): Route;

    /**
     * Add GET route. Also add the HEAD method because if you can do a GET request, you can also implicitly do a HEAD request.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.1
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.3
     *
     * @param string          $pattern The route URI pattern
     * @param callable|string $handler The route callback routine
     *
     * @return \Chiron\Routing\Route
     */
    public function get(string $pattern, $handler): Route
    {
        return $this->map($pattern, $handler)->method('GET');
    }

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
    public function head(string $pattern, $handler): Route
    {
        return $this->map($pattern, $handler)->method('HEAD');
    }

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
    public function post(string $pattern, $handler): Route
    {
        return $this->map($pattern, $handler)->method('POST');
    }

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
    public function put(string $pattern, $handler): Route
    {
        return $this->map($pattern, $handler)->method('PUT');
    }

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
    public function delete(string $pattern, $handler): Route
    {
        return $this->map($pattern, $handler)->method('DELETE');
    }

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
    public function options(string $pattern, $handler): Route
    {
        return $this->map($pattern, $handler)->method('OPTIONS');
    }

    /**
     * Add TRACE route.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.8
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.8
     *
     * @param string          $pattern The route URI pattern
     * @param callable|string $handler The route callback routine
     *
     * @return \Chiron\Routing\Route
     */
    public function trace(string $pattern, $handler): Route
    {
        return $this->map($pattern, $handler)->method('TRACE');
    }

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
    public function patch(string $pattern, $handler): Route
    {
        return $this->map($pattern, $handler)->method('PATCH');
    }

    /**
     * Add route for any HTTP method.
     * Supports the following methods : 'GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'TRACE'.
     *
     * @param string          $pattern The route URI pattern
     * @param callable|string $handler The route callback routine
     *
     * @return \Chiron\Routing\Route
     */
    public function any(string $pattern, $handler): Route
    {
        return $this->map($pattern, $handler);
    }

    /**
     * Create a redirect from one URI to another.
     *
     * @param string $url
     * @param string $destination
     * @param int    $status
     *
     * @return \Chiron\Routing\Route
     */
    public function redirect(string $url, string $destination, int $status = 302): Route
    {
        return $this->any($url, '\Chiron\Routing\Controller\RedirectController@__invoke')
                ->setDefault('destination', $destination)
                ->setDefault('status', $status);
    }

    /**
     * Create a permanent redirect from one URI to another.
     *
     * @param string $url
     * @param string $destination
     *
     * @return \Chiron\Routing\Route
     */
    public function permanentRedirect(string $url, string $destination): Route
    {
        return $this->redirect($url, $destination, 301);
    }

    /**
     * Register a new route that returns a view.
     *
     * @param string $url
     * @param string $view
     * @param array  $params
     *
     * @return \Chiron\Routing\Route
     */
    public function view(string $url, string $view, array $params = []): Route
    {
        return $this->match(['GET', 'HEAD'], $url, '\Chiron\Routing\Controller\ViewController@__invoke')
                ->setDefault('view', $view)
                ->setDefault('params', $params);
    }
}
