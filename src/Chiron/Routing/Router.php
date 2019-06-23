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
//https://github.com/Wandu/Router/blob/master/RouteCollection.php

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
    // TODO : regarder ici : https://github.com/ncou/router-group-middleware/blob/master/src/Router/Router.php#L25
    // TODO : regarder ici : https://github.com/ncou/php-router-group-middleware/blob/master/src/Router.php#L26
    // TODO : faire un tableau plus simple et ensuite dans le constructeur faire un array walk pour ajouter ces patterns.
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

    /*
    //https://github.com/codeigniter4/CodeIgniter4/blob/develop/system/Router/RouteCollection.php#L122

    protected $placeholders = [
        'any'      => '.*',
        'segment'  => '[^/]+',
        'alphanum' => '[a-zA-Z0-9]+',
        'num'      => '[0-9]+',
        'alpha'    => '[a-zA-Z]+',
        'hash'     => '[^/]+',
    ];


    */

    /**
     * @var string Can be used to ignore leading part of the Request URL (if main file lives in subdirectory of host)
     */
    private $basePath = '';

    /**
     * Constructor.
     *
     * @param \FastRoute\RouteParser   $parser
     * @param \FastRoute\DataGenerator $generator
     */
    public function __construct(DataGenerator $generator = null)
    {
        $this->parser = new RouteParser\Std();
        // build parent route collector
        $this->generator = ($generator) ?? new DataGenerator\GroupCountBased();

        // TODO utiliser ce bout de code et faire un tableau de pattern dans la classe de ce type ['slug' => 'xxxx', 'number' => 'yyyy']
/*
        array_walk($this->patternMatchers, function ($value, $key) {
            $this->addPatternMatcher($key, $value);
        });*/
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
            throw new InvalidArgumentException('Route Handler should be a callable or a string (service name in the container or class name).');
        }

        // TODO : attention vérifier si cette modification du path avec un slash n'est pas en doublon avec celle qui est faite dans la classe Route !!!!
        $path = sprintf('/%s', ltrim($path, '/'));
        $route = new Route($path, $handler);

        $this->routes[uniqid('UID_', true)] = $route;

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
    // TODO : vérifier si on pas plutot utiliser un Closure au lieu d'un callable pour le typehint.
    // TODO : il semble pôssible dans Slim de passer une string, ou un callable. Vérifier l'utilité de cette possibilité d'avoir un string !!!!
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
            $this->setStrategy(new HtmlStrategy);
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
        $this->processGroups();

        foreach ($this->routes as $identifier => $route) {
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

            $this->injectRoute($identifier, $route->getAllowedMethods(), $this->basePath . $routePath);
        }
    }

    /**
     * Process all groups.
     */
    // A voir si cette méthode ne devrait pas être appellée directement dans la méthode ->group() pour préparer les routes dés qu'on ajoute un group !!!!
    // https://github.com/slimphp/Slim/blob/4.x/Slim/Routing/RouteCollector.php#L255
    private function processGroups(): void
    {
        // TODO : vérifier si il ne faut pas faire un array_reverse lorsqu'on execute les groups. Surtout dans le cas ou on ajoute des middlewares au group et qui seront propagés à la route.
        //https://github.com/slimphp/Slim/blob/4.x/Slim/Routing/Route.php#L350

        // Call the $group by reference because in the case : group of group the size of the array is modified because a new group is added in the group() function.
        foreach ($this->groups as $key => &$group) {
            // TODO : déplacer le unset aprés la méthode invoke ou collectroute du group. Voir si c'est pas plus ^propre de remplacer le unset par un array_pop ou un array_shift !!!!
            unset($this->groups[$key]);
            // TODO : créer une méthode ->collectRoutes() dans la classe RouteGroup, au lieu d'utiliser le invoke() on utilisera cette méthode, c'est plus propre !!!!
            $group();
            //array_pop($this->groups);
            //array_shift($this->routeGroups);
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
     * @param string   $routeId
     * @param string[] $httpMethod
     * @param string   $routePath
     */
    private function injectRoute(string $routeId, array $httpMethod, string $routePath): void
    {
        $routeDatas = $this->parser->parse($routePath);
        foreach ($httpMethod as $method) {
            foreach ($routeDatas as $routeData) {
                // TODO : réactiver le try catch si on souhaite pouvoir gérer les doublons de routes.
                //try {
                $this->generator->addRoute($method, $routeData, $routeId);
                //} catch (\Throwable $e) {
                //}
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
        //unset($this->routes[$route->getIdentifier()]);
        // no exception so far so the route exists we can remove the object safely.
        unset($this->routes[array_search($route, $this->routes)]);
    }

    /*
     * {@inheritdoc}
     */
    /*
    public function lookupRoute(string $identifier): Route
    {
        if (!isset($this->routes[$identifier])) {
            throw new InvalidArgumentException('Route not found for identifier: ' . $identifier);
        }
        return $this->routes[$identifier];
    }*/

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
