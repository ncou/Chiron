<?php

declare(strict_types=1);

namespace Chiron\Routing;

use Chiron\MiddlewareAwareInterface;
use Chiron\MiddlewareAwareTrait;
use Chiron\Routing\Strategy\StrategyAwareInterface;
use Chiron\Routing\Strategy\StrategyAwareTrait;
use Chiron\Routing\Strategy\StrategyInterface;
use FastRoute\DataGenerator;
use FastRoute\Dispatcher as FastRoute;
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
class Router implements RouteCollectionInterface, StrategyAwareInterface //, MiddlewareAwareInterface
{
    //use MiddlewareAwareTrait;
    use RouteCollectionTrait;
    use StrategyAwareTrait; // vérifier pourquoi on utilise un StrategyAware, normalement on devrait utiliser uniquement le det/setDefaultStrategy

    /** @var FastRoute\RouteParser */
    private $parser;

    /** @var FastRoute\DataGenerator */
    private $generator;

    /**
     * @var \League\Route\Route[]
     */
    private $routes = [];

    /**
     * @var \League\Route\Route[]
     */
    private $namedRoutes = [];

    /**
     * @var \League\Route\RouteGroup[]
     */
    private $groups = [];

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
        $this->routes[] = $route;
        $this->routeCounter++;

        return $route;
    }

    /**
     * Add a group of routes to the collection.
     *
     * @param string   $prefix
     * @param callable $group
     *
     * @return \League\Route\RouteGroup
     */
    public function group(string $prefix, callable $group): RouteGroup
    {
        $group = new RouteGroup($prefix, $group, $this);
        $this->groups[] = $group;

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

        /*
                return (new Dispatcher($this->getData()))
                    ->middlewares($this->getMiddlewareStack())
                    ->setStrategy($this->getStrategy())
                    ->dispatchRequest($request)
                ;*/

        // process routes
        $dispatcher = new \FastRoute\Dispatcher\GroupCountBased($this->generator->getData());

        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        $result = $dispatcher->dispatch($method, $path);

        return $result[0] !== FastRoute::FOUND
            ? $this->marshalFailedRoute($result)
            : $this->marshalMatchedRoute($result, $method);
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
    protected function prepareRoutes(ServerRequestInterface $request): void
    {
        $this->processGroups($request);
        $this->buildNameIndex();
        $routes = array_merge(array_values($this->routes), array_values($this->namedRoutes));
        foreach ($routes as $key => $route) {
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
     * Build an index of named routes.
     */
    protected function buildNameIndex(): void
    {
        foreach ($this->routes as $key => $route) {
            if (! is_null($route->getName())) {
                unset($this->routes[$key]);
                $this->namedRoutes[$route->getName()] = $route;
            }
        }
    }

    /**
     * Process all groups.
     *
     * Adds all of the group routes to the collection and determines if the group
     * strategy should be be used.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    protected function processGroups(ServerRequestInterface $request): void
    {
        $activePath = $request->getUri()->getPath();
        foreach ($this->groups as $key => $group) {
            // we want to determine if we are technically in a group even if the
            // route is not matched so exceptions are handled correctly
            // TODO : vérifier que ce bout de code ne sert à rien !!!!
            /*
            if (strncmp($activePath, $group->getPrefix(), strlen($group->getPrefix())) === 0
                && ! is_null($group->getStrategy())
            ) {
                $this->setStrategy($group->getStrategy());
            }*/
            unset($this->groups[$key]);
            $group();
        }
    }

    /**
     * Get a named route.
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException when no route of the provided name exists.
     *
     * @return \League\Route\Route
     */
    public function getNamedRoute(string $name): Route
    {
        $this->buildNameIndex();
        if (isset($this->namedRoutes[$name])) {
            return $this->namedRoutes[$name];
        }

        throw new InvalidArgumentException(sprintf('No route of the name (%s) exists', $name));
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
     * Marshal a routing failure result.
     *
     * If the failure was due to the HTTP method, passes the allowed HTTP
     * methods to the factory.
     */
    private function marshalFailedRoute(array $result): RouteResult
    {
        if ($result[0] === FastRoute::METHOD_NOT_ALLOWED) {
            return RouteResult::fromRouteFailure($result[1]);
        }

        return RouteResult::fromRouteFailure(RouteResult::HTTP_METHOD_ANY);
    }

    /**
     * Marshals a route result based on the results of matching and the current HTTP method.
     */
    private function marshalMatchedRoute(array $result, string $method): RouteResult
    {
        $identifier = $result[1];

        $route = $this->lookupRoute($identifier);

        $params = $result[2];

        /*
        $options = $route->getOptions();
        if (! empty($options['defaults'])) {
            $params = array_merge($options['defaults'], $params);
        }*/

        return RouteResult::fromRoute($route, $params);
    }

    /**
     * @param string $identifier
     *
     * @return RouteInterface
     */
    // TODO : améliorer ce bout de code !!!!!
    private function lookupRoute(string $identifier): Route
    {
        /*
        if (!isset($this->routes[$identifier])) {
            throw new RuntimeException('Route not found.');
        }
        return $this->routes[$identifier];
        */

        $routes = array_merge(array_values($this->routes), array_values($this->namedRoutes));

        foreach ($routes as $route) {
            if ($route->getIdentifier() === $identifier) {
                return $route;
            }
        }

        throw new \RuntimeException('Route not found for the given identifier.');
    }
}
