<?php

declare(strict_types=1);

namespace Chiron\Routing;

use Chiron\Routing\Traits\MiddlewareAwareInterface;
use Chiron\Routing\Traits\MiddlewareAwareTrait;
use Chiron\Routing\Traits\RouteCollectionInterface;
use Chiron\Routing\Traits\RouteCollectionTrait;
use Chiron\Routing\Traits\StrategyAwareInterface;
use Chiron\Routing\Traits\StrategyAwareTrait;
use FastRoute\DataGenerator;
use FastRoute\RouteParser;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

//https://github.com/zendframework/zend-expressive-fastroute/blob/master/src/FastRouteRouter.php

// TODO : il manque head et options dans la phpdoc
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
class Router implements RouterInterface, StrategyAwareInterface, RouteCollectionInterface, MiddlewareAwareInterface
{
    use MiddlewareAwareTrait;
    use RouteCollectionTrait;
    use StrategyAwareTrait;

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
    private $groups = [];

    /**
     * @var array
     */
    private $patternMatchers = [
        '/{(.+?):number}/'        => '{$1:[0-9]+}',
        '/{(.+?):word}/'          => '{$1:[a-zA-Z]+}',
        '/{(.+?):alphanum_dash}/' => '{$1:[a-zA-Z0-9-_]+}',
        '/{(.+?):slug}/'          => '{$1:[a-z0-9-]+}',
        '/{(.+?):uuid}/'          => '{$1:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}',
    ];

    /*

    ':any' => '[^/]+',
    ':all' => '.*'


    '*'  => '.+?',
    '**' => '.++',


    */

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
     * Set the base path.
     * Useful if you are running your application from a subdirectory.
     */
    public function setBasePath(string $basePath): void
    {
        $this->basePath = rtrim($basePath, '/');
        //$this->basePath = $basePath;
    }

    /**
     * Get the router base path.
     * Useful if you are running your application from a subdirectory.
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * {@inheritdoc}
     */
    public function map(string $path, $handler): Route
    {
        // TODO : il faudrait peut etre remonter ce controle durectement dans l'objet Route() non ????
        if (! is_string($handler) && ! is_callable($handler)) {
            throw new InvalidArgumentException('Route Handler should be a callable or a string (if defined in the container).');
        }

        // TODO : attention vérifier si cette modification du path avec un slash n'est pas en doublon avec celle qui est faite dans la classe Route !!!!
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
    public function group(string $prefix, callable $callback): RouteGroup
    {
        $group = new RouteGroup($prefix, $callback, $this);
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
        $this->processGroups();
        $this->injectRoutes($request);

        // process routes
        $dispatcher = new Dispatcher($this->routes, $this->generator->getData());

        return $dispatcher->dispatchRequest($request);
    }

    /**
     * Prepare all routes, build name index and filter out none matching
     * routes before being passed off to the parser.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    private function injectRoutes(ServerRequestInterface $request): void
    {
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

            // add a route strategy if no one is defined. Use the default router strategy.
            if (is_null($route->getStrategy())) {
                // Throw an exception if there is not a default strategy defined.
                if (is_null($this->getStrategy())) {
                    throw new RuntimeException('A defaut strategy should be defined in the Router, as there is no specific strategy defined for the Route.');
                }
                $route->setStrategy($this->getStrategy());
            }

            $routePath = $this->replaceAssertPatterns($route->getRequirements(), $route->getPath());
            $routePath = $this->replaceWordPatterns($routePath);

            $this->addRoute($route->getAllowedMethods(), $this->basePath . $routePath, $route->getIdentifier());
        }
    }

    /**
     * Process all groups.
     */
    private function processGroups(): void
    {
        // Call the $group by reference because in the case : group of group the size of the array is modified because a new group is added in the group() function.
        foreach ($this->groups as $key => &$group) {
            unset($this->groups[$key]);
            $group();
            //array_pop($this->groups);
        }
    }

    /**
     * Add or replace the requirement pattern inside the route path.
     *
     * @param array  $requirements
     * @param string $path
     *
     * @return string
     */
    private function replaceAssertPatterns(array $requirements, string $path): string
    {
        $patternAssert = [];
        foreach ($requirements as $attribute => $pattern) {
            // it will replace {attribute_name} to {attribute_name:$pattern}, work event if there is alreay a patter {attribute_name:pattern_to_remove} to {attribute_name:$pattern}
            // the second regex group (starting with the char ':') will be discarded.
            $patternAssert['/{(' . $attribute . ')(\:.*)?}/'] = '{$1:' . $pattern . '}';
            //$patternAssert['/{(' . $attribute . ')}/'] = '{$1:' . $pattern . '}'; // TODO : réfléchir si on utilise cette regex, dans ce cas seulement les propriétés qui n'ont pas déjà un pattern de défini (c'est à dire une partie avec ':pattern')
        }

        return preg_replace(array_keys($patternAssert), array_values($patternAssert), $path);
    }

    /**
     * Replace word patterns with regex in route path.
     *
     * @param string $path
     *
     * @return string
     */
    private function replaceWordPatterns(string $path): string
    {
        return preg_replace(array_keys($this->patternMatchers), array_values($this->patternMatchers), $path);
    }

    /**
     * Adds a route to the collection.
     *
     * The syntax used in the $route string depends on the used route parser.
     *
     * @param string[] $httpMethod
     * @param string   $routePath
     * @param string   $routeId
     */
    private function addRoute(array $httpMethod, string $routePath, string $routeId): void
    {
        $routeDatas = $this->parser->parse($routePath);
        foreach ($httpMethod as $method) {
            foreach ($routeDatas as $routeData) {
                $this->generator->addRoute($method, $routeData, $routeId);
            }
        }
    }

    /**
     * Get route objects.
     *
     * @return Route[]
     */
    public function getRoutes(): array
    {
        $this->processGroups();

        return array_values($this->routes);
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
        foreach ($this->getRoutes() as $route) {
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
     * Build the path for a named route including the base path.
     *
     * @param string $name        Route name
     * @param array  $data        Named argument replacement data
     * @param array  $queryParams Optional query string parameters
     *
     * @throws InvalidArgumentException If named route does not exist
     * @throws InvalidArgumentException If required data not provided
     *
     * @return string
     */
    public function pathFor(string $name, array $data = [], array $queryParams = []): string
    {
        $url = $this->relativePathFor($name, $data, $queryParams);

        if ($this->basePath) {
            $url = $this->basePath . $url;
        }

        return $url;
    }

    /**
     * Build the path for a named route excluding the base path.
     *
     * @param string $name          Route name
     * @param array  $substitutions Named argument replacement data
     * @param array  $queryParams   Optional query string parameters
     *
     * @throws InvalidArgumentException If named route does not exist
     * @throws InvalidArgumentException If required data not provided
     *
     * @return string
     */
    // TODO : renommer cette fonction en "generateUri()", et renommer le paramétre "$data" en "$substitutions" et éventuellement virer la partie $queryParams ???? non ????
    // TODO : ajouter la gestion des segments en plus des query params ???? https://github.com/ellipsephp/url-generator/blob/master/src/UrlGenerator.php#L42
    // TODO : regarder si on peut améliorer le code => https://github.com/zendframework/zend-expressive-fastroute/blob/master/src/FastRouteRouter.php#L239
    public function relativePathFor(string $name, array $substitutions = [], array $queryParams = []): string
    {
        $route = $this->getNamedRoute($name);
        $pattern = $route->getPath();
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
                if (! array_key_exists($item[0], $substitutions)) {
                    // we don't have a data element for this segment: cancel
                    // testing this routeData item, so that we can try a less
                    // specific routeData item.
                    $segments = [];
                    $segmentName = $item[0];

                    break;
                }
                $segments[] = $substitutions[$item[0]];
            }
            if (! empty($segments)) {
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

    /*
     * Determine if the route is duplicated in the current list.
     *
     * Checks if a route with the same name or path exists already in the list;
     * if so, and it responds to any of the $methods indicated, raises
     * a DuplicateRouteException indicating a duplicate route.
     *
     * @throws Exception\DuplicateRouteException on duplicate route detection.
     */
    //https://github.com/zendframework/zend-expressive-router/blob/master/src/RouteCollector.php#L149
    /*
    private function checkForDuplicateRoute(string $path, array $methods = null) : void
    {
        if (null === $methods) {
            $methods = Route::HTTP_METHOD_ANY;
        }
        $matches = array_filter($this->routes, function (Route $route) use ($path, $methods) {
            if ($path !== $route->getPath()) {
                return false;
            }
            if ($methods === Route::HTTP_METHOD_ANY) {
                return true;
            }
            return array_reduce($methods, function ($carry, $method) use ($route) {
                return ($carry || $route->allowsMethod($method));
            }, false);
        });
        if (! empty($matches)) {
            $match = reset($matches);
            $allowedMethods = $match->getAllowedMethods() ?: ['(any)'];
            $name = $match->getName();
            throw new Exception\DuplicateRouteException(sprintf(
                'Duplicate route detected; path "%s" answering to methods [%s]%s',
                $match->getPath(),
                implode(',', $allowedMethods),
                $name ? sprintf(', with name "%s"', $name) : ''
            ));
        }
    }*/
}
