<?php

declare(strict_types=1);

namespace Chiron\Routing;

use Chiron\Routing\Strategy\StrategyAwareInterface;
use Chiron\Routing\Strategy\StrategyAwareTrait;
use Chiron\Routing\Strategy\StrategyInterface;
use FastRoute\DataGenerator;
use FastRoute\RouteParser;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Aggregate routes for the router.
 *
 * This class provides * methods for creating path+HTTP method-based routes and
 * injecting them into the router:
 *
 * - get
 * - post
 * - put
 * - patch
 * - delete
 * - any
 *
 * A general `route()` method allows specifying multiple request methods and/or
 * arbitrary request methods when creating a path-based route.
 *
 * Internally, the class performs some checks for duplicate routes when
 * attaching via one of the exposed methods, and will raise an exception when a
 * collision occurs.
 */
class Router implements RouteCollectionInterface, StrategyAwareInterface, MiddlewareAwareInterface
{
    use MiddlewareAwareTrait;
    use RouteCollectionTrait;
    use StrategyAwareTrait; // vérifier pourquoi on utilise un StrategyAware, normalement on devrait utiliser uniquement le det/setDefaultStrategy

    /** @var FastRoute\RouteParser */
    private $parser;

    /** @var FastRoute\DataGenerator */
    private $generator;

    /**
     * @var \Chiron\Routing\Route[]
     */
    private $routes = [];

    /**
     * @var \Chiron\Routing\RouteGroup[]
     */
    private $groups = []; // TODO : vérifier l'utilité d'avoir un tableau de groups !!!!

    /**
     * @var array
     */
    private $patternMatchers = [
        '/{(.+?):number}/'        => '{$1:[0-9]+}',
        '/{(.+?):word}/'          => '{$1:[a-zA-Z]+}',
        '/{(.+?):alphanum_dash}/' => '{$1:[a-zA-Z0-9-_]+}',
        '/{(.+?):slug}/'          => '{$1:[a-z0-9-]+}',
        '/{(.+?):uuid}/'          => '{$1:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}+}',
    ];

    /**
     * @var string Can be used to ignore leading part of the Request URL (if main file lives in subdirectory of host)
     */
    private $basePath = '';

    /**
     * Route counter incrementer.
     *
     * @var int
     */
    private $routeCounter = 0;

    /** StrategyInterface */
    // TODO : à virer et utiliser plutot le StrategyAwareTrait
    private $defaultStrategy;

    /**
     * Constructor.
     *
     * @param \FastRoute\RouteParser   $parser
     * @param \FastRoute\DataGenerator $generator
     */
    public function __construct(RouteParser $parser = null, DataGenerator $generator = null)
    {
        // build parent route collector
        $this->parser = ($parser) ?? new RouteParser\Std();
        $this->generator = ($generator) ?? new DataGenerator\GroupCountBased();
    }

    /**
     * Set the base path.
     * Useful if you are running your application from a subdirectory.
     */
    public function setBasePath(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
        //$this->basePath = $basePath;
    }

    /**
     * Get the router base path.
     * Useful if you are running your application from a subdirectory.
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * {@inheritdoc}
     */
    public function map(string $path, $handler): Route
    {
        if (! is_string($handler) && ! is_callable($handler)) {
            throw new InvalidArgumentException('Route Handler should be a callable or a string (if defined in the container).');
        }

        $path = sprintf('/%s', ltrim($path, '/'));
        $route = new Route($path, $handler, $this->routeCounter);
        $this->routes[$route->getIdentifier()] = $route;
        $this->routeCounter++;

        return $route;
    }

    /**
     * Add a group of routes to the collection.
     *
     * @param string   $prefix
     * @param callable $group
     *
     * @return \Chiron\Routing\RouteGroup
     */
    // TODO : vérifier si on pas plutot utiliser un Closure au lieu d'un callable pour le typehint
    public function group(string $prefix, callable $group): RouteGroup
    {
        $group = new RouteGroup($prefix, $group, $this);
        // TODO : vérifier l'utilité d'avoir un tableau de groups !!!!
        $this->groups[] = $group;

        $group();
        array_pop($this->groups); // TODO : vérifier l'utilité d'avoir un tableau de groups !!!!

        return $group;
    }

    /**
     * {@inheritdoc}
     */
    /*
    public function dispatch_OLD(ServerRequestInterface $request) : ResponseInterface
    {
        if (is_null($this->getStrategy())) {
            $this->setStrategy(new ApplicationStrategy);
        }

        $this->prepareRoutes($request);

        return (new Dispatcher($this->getData()))
            ->middlewares($this->getMiddlewareStack())
            ->setStrategy($this->getStrategy())
            ->dispatchRequest($request)
        ;
    }*/

    public function match(ServerRequestInterface $request): RouteResult
    {
        // TODO : à améliorer !!!!
        if (is_null($this->getStrategy())) {
            $this->setStrategy($this->getDefaultStrategy());
        }

        $this->prepareRoutes($request);

        // process routes
        $dispatcher = new Dispatcher($this->routes, $this->generator->getData());

        return $dispatcher->dispatchRequest($request);
    }

    public function getDefaultStrategy(): StrategyInterface
    {
        return $this->defaultStrategy;
    }

    public function setDefaultStrategy(StrategyInterface $strategy): self
    {
        $this->defaultStrategy = $strategy;

        return $this;
    }

    /**
     * Prepare all routes, build name index and filter out none matching
     * routes before being passed off to the parser.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    private function prepareRoutes(ServerRequestInterface $request): void
    {
        //$this->processGroups();

        foreach ($this->routes as $key => $route) {
            // check for scheme condition
            if (! is_null($route->getScheme()) && $route->getScheme() !== $request->getUri()->getScheme()) {
                continue;
            }
            // check for domain condition
            if (! is_null($route->getHost()) && $route->getHost() !== $request->getUri()->getHost()) {
                continue;
            }
            // check for port condition
            if (! is_null($route->getPort()) && $route->getPort() !== $request->getUri()->getPort()) {
                continue;
            }
            // add a route strategy if no one is defined
            if (is_null($route->getStrategy())) {
                $route->setStrategy($this->getStrategy());
            }

            $this->addRoute($route->getAllowedMethods(), $this->parseRoutePath($route->getUrl()), $route->getIdentifier());
        }
    }

    /**
     * Adds a route to the collection.
     *
     * The syntax used in the $route string depends on the used route parser.
     *
     * @param string|string[] $httpMethod
     * @param string          $route
     * @param mixed           $handler
     */
    private function addRoute($httpMethod, string $route, $handler): void
    {
        $route = $this->basePath . $route;
        $routeDatas = $this->parser->parse($route);
        foreach ((array) $httpMethod as $method) {
            foreach ($routeDatas as $routeData) {
                $this->generator->addRoute($method, $routeData, $handler);
            }
        }
    }

    /**
     * Get route objects
     *
     * @return Route[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Get a named route.
     *
     * @param string $name Route name
     *
     * @throws \InvalidArgumentException If named route does not exist
     *
     * @return \Chiron\Routing\Route
     */
    public function getNamedRoute(string $name): Route
    {
        foreach ($this->routes as $route) {
            if ($route->getName() === $name) {
                return $route;
            }
        }

        throw new InvalidArgumentException('Named route does not exist for name: ' . $name);
    }

    /**
     * Remove named route.
     *
     * @param string $name Route name
     *
     * @throws \InvalidArgumentException If named route does not exist
     */
    public function removeNamedRoute(string $name)
    {
        $route = $this->getNamedRoute($name);
        // no exception, route exists, now remove by id
        unset($this->routes[$route->getIdentifier()]);
    }

    /**
     * Add a convenient pattern matcher to the internal array for use with all routes.
     *
     * @param string $alias
     * @param string $regex
     *
     * @return self
     */
    public function addPatternMatcher(string $alias, string $regex): self
    {
        $pattern = '/{(.+?):' . $alias . '}/';
        $regex = '{$1:' . $regex . '}';
        $this->patternMatchers[$pattern] = $regex;

        return $this;
    }

    /**
     * Replace word patterns with regex in route path.
     *
     * @param string $path
     *
     * @return string
     */
    protected function parseRoutePath(string $path): string
    {
        return preg_replace(array_keys($this->patternMatchers), array_values($this->patternMatchers), $path);
    }

    /**
     * Build the path for a named route excluding the base path
     *
     * @param string $name        Route name
     * @param array  $data        Named argument replacement data
     * @param array  $queryParams Optional query string parameters
     *
     * @return string
     *
     * @throws InvalidArgumentException         If named route does not exist
     * @throws InvalidArgumentException If required data not provided
     */
    public function relativePathFor(string $name, array $data = [], array $queryParams = []): string
    {
        $route = $this->getNamedRoute($name);
        $pattern = $route->getUrl();
        $routeDatas = $this->parser->parse($pattern);
        // $routeDatas is an array of all possible routes that can be made. There is
        // one routedata for each optional parameter plus one for no optional parameters.
        //
        // The most specific is last, so we look for that first.
        $routeDatas = array_reverse($routeDatas);
        $segments = [];
        foreach ($routeDatas as $routeData) {
            foreach ($routeData as $item) {
                if (is_string($item)) {
                    // this segment is a static string
                    $segments[] = $item;
                    continue;
                }
                // This segment has a parameter: first element is the name
                if (!array_key_exists($item[0], $data)) {
                    // we don't have a data element for this segment: cancel
                    // testing this routeData item, so that we can try a less
                    // specific routeData item.
                    $segments = [];
                    $segmentName = $item[0];
                    break;
                }
                $segments[] = $data[$item[0]];
            }
            if (!empty($segments)) {
                // we found all the parameters for this route data, no need to check
                // less specific ones
                break;
            }
        }
        if (empty($segments)) {
            throw new InvalidArgumentException('Missing data for URL segment: ' . $segmentName);
        }
        $url = implode('', $segments);
        if ($queryParams) {
            $url .= '?' . http_build_query($queryParams);
        }
        return $url;
    }
    /**
     * Build the path for a named route including the base path
     *
     * @param string $name        Route name
     * @param array  $data        Named argument replacement data
     * @param array  $queryParams Optional query string parameters
     *
     * @return string
     *
     * @throws InvalidArgumentException         If named route does not exist
     * @throws InvalidArgumentException If required data not provided
     */
    public function pathFor(string $name, array $data = [], array $queryParams = []): string
    {
        $url = $this->relativePathFor($name, $data, $queryParams);
        if ($this->basePath) {
            $url = $this->basePath . $url;
        }
        return $url;
    }
}
