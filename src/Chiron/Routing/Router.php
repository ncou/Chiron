<?php

declare(strict_types=1);

// TODO : créer une classe RouteResult() au lieu de retourner un tableau avec FOUNT / NOT_FOUND / METHOD_NOT_ALLOWED : https://github.com/zendframework/zend-expressive-router/blob/master/src/RouteResult.php

// TODO : créer une interface : https://github.com/zendframework/zend-expressive-router/blob/master/src/RouterInterface.php

// TODO : regarder ici pour gérer les doublons de routes, et lever une exception si on essaye de redéclarer une route !!!!   https://github.com/userfrosting/UserFrosting/blob/master/app/sprinkles/core/src/Router.php#L66

// TODO réfléchir si il faut encoder et décoder les url via un rawurlencode/decode pour s'assurer que les caractéres genre "espace => %20" sont bien gérés

// TODO regarder le router de cake : https://book.cakephp.org/3.0/fr/development/routing.html
/*
$routes->connect(
    '/:lang/articles/:slug',
    ['controller' => 'Articles', 'action' => 'view'],
)
// Allow GET and POST requests.
->setMethods(['GET', 'POST'])

// Only match on the blog subdomain.
->setHost('blog.example.com')

// Set the route elements that should be converted to passed arguments
->setPass(['slug'])

// Set the matching patterns for route elements
->setPatterns([
    'slug' => '[a-z0-9-_]+',
    'lang' => 'en|fr|es',
])

// Also allow JSON file extensions
->setExtensions(['json'])

*/

namespace Chiron\Routing;

// TODO : utiliser un systéme de cache avec un serialize/unserialize pour charger les routes en cache : https://github.com/symfony/routing/blob/master/Route.php#L68
// TODO : regarder ici pour gérer le cache : http://tech.zumba.com/2014/10/26/cakephp-caching-routes/
// TODO : Cache : https://github.com/nikic/FastRoute/blob/0bc798647892cfe4320063c40b62bc81b6ca559f/src/functions.php#L63
// TODO ; cache : https://github.com/ncou/router.php-cache

// TODO : utilisation d'un cache : https://github.com/timtegeler/routerunner/blob/master/src/Components/Cache.php

// TODO : middleware router : https://github.com/timtegeler/routerunner/blob/master/src/Routerunner.php

// TODO : ajouter une propriété pour gérer les url de maniére non-case sensitive : https://github.com/mastacontrola/AltoRouter/blob/master/AltoRouter.php#L541

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

/**
 * Router class.
 *
 * This class contains list of application routes,
 * also routes serves as route factory and
 * here is defined run method for routing requests.
 *
 * @author <milos@caenazzo.com>
 */
class Router
{
    public const NOT_FOUND = 0;
    public const FOUND = 1;
    public const METHOD_NOT_ALLOWED = 2;

    /**
     * Collection of routes.
     *
     * @var RouteInterface[]
     */
    protected $routes = [];

    /**
     * @var string Can be used to ignore leading part of the Request URL (if main file lives in subdirectory of host)
     */
    protected $basePath = '';

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
     * Add named match types. It uses array_merge so keys can be overwritten.
     *
     * @param array $matchTypes The key is the name and the value is the regex.
     */
    // TODO : passer au contructeur du routeur une liste de nouveaux matchTypes possibles. Cela permettra de passer ensuite ce nouyveau tableau à l'objet route dans son constructeur pour qu'il fusionne les regex possible
    /*
    public function addMatchTypes(array $matchTypes) {
        $this->matchTypes = array_merge($this->matchTypes, $matchTypes);
    }
*/

    /**
     * Reversed routing.
     *
     * Generate the URL for a named route. Replace regexes pattern with supplied substitutions
     *
     * @param string $name          Route name.
     * @param array  $substitutions Key/value pairs to substitute into the route pattern.
     *
     * @throws Exception\RuntimeException if the route name is not known
     *                                    or a parameter value does not match its regex.
     *
     * @return string URI path generated.
     */
    public function generateUri(string $routeName, array $substitutions = []): string
    {
        // TODO : la méthode getNamedRoute retourne déjà une exception si la route n'existe pas !!! code à virer ? ou alors il faut modifier le getNamedRoute() pour qu'il retourne vide au lieu d'une exception ????
        // Check if named route exists
        if (! $this->hasRoute($routeName)) {
            throw new RuntimeException();
        }

        $route = $this->getNamedRoute($routeName);
        // TODO : lever une exception si on a trouvé plusieurs routes avec le même nom !!!!

        $url = $route->generate($substitutions);

        return $url;
    }

    /**
     * Dispatches against the provided HTTP method verb and URI.
     *
     * Returns array with one of the following formats:
     *
     *     [UrlMatcher::NOT_FOUND]
     *     [UrlMatcher::METHOD_NOT_ALLOWED, ['GET', 'OTHER_ALLOWED_METHODS']]
     *     [UrlMatcher::FOUND, $route, ['varName' => 'value', ...]]
     *
     * @param string $httpMethod
     * @param string $uri
     *
     * @return array
     */
    //TODO : méthode à renommer en "dispatch" ????
    public function match(ServerRequestInterface $request): RouteResult
    {
        $requestUrl = $request->getUri()->getPath();
        $requestMethod = $request->getMethod();
        $requestScheme = $request->getScheme();

        $allowedMethods = [];

        foreach ($this->routes as $route) {
            $params = [];
            $match = false;

            // check condition for "Http or Https" required
            if ($route->getSchemes() && ! $route->hasScheme($requestScheme)) {
                continue;
            }

            // check URL path
            if ($route->getUrl() === '*') {
                // * wildcard (matches all)
                $match = true;
            } elseif (($position = strpos($route->getUrl(), '[')) === false) {
                // No params in url, do string comparison
                $match = strcmp($requestUrl, $route->getUrl()) === 0;
            } else {
                // Compare longest non-param string with url
                if (strncmp($requestUrl, $route->getUrl(), $position) === 0) {
                    $regex = $route->compile($route->getUrl());
                    $match = preg_match($regex, $requestUrl, $params) === 1;
                }
            }

            if ($match) {
                // check HTTP method requirement
                if ($requiredMethods = $route->getAllowedMethods()) {
                    // HEAD and GET are equivalent as per RFC
                    if ('HEAD' === $method = $requestMethod) {
                        $method = 'GET';
                    }
                    if (! in_array($method, $requiredMethods)) {
                        $allowedMethods = array_merge($allowedMethods, $requiredMethods);
                        continue;
                    }
                }

                //return [self::FOUND, $route, $this->mergeDefaults($params, $route->getDefaults())];
                return RouteResult::fromRoute($route, $this->mergeDefaults($params, $route->getDefaults()));
            }
        }

        // If there are no allowed methods the route simply does not exist
        if ($allowedMethods) {
            //return [self::METHOD_NOT_ALLOWED, array_unique($allowedMethods)];
            return RouteResult::fromRouteFailure(array_unique($allowedMethods));
        }
        //return [self::NOT_FOUND];
        return RouteResult::fromRouteFailure(RouteResult::HTTP_METHOD_ANY);
    }

    /**
     * Get merged default parameters.
     *
     * @param array $params   The parameters
     * @param array $defaults The defaults
     *
     * @return array Merged default parameters
     */
    private function mergeDefaults(array $params, array $defaults): array
    {
        foreach ($params as $key => $value) {
            //preg_match includes matches twice: once by their name, and once with the numeric index.
            // So we ignore the numeric keys since all the required params are named
            if (! is_int($key)) {
                $defaults[$key] = $value;
            }
        }

        return $defaults;
    }

    /**
     * Add route with multiple methods (by defaul accept any HTTP method).
     *
     * @param string                  $pattern The route URI pattern
     * @param RequestHandlerInterface $handler The route callback routine
     *
     * @return RouteInterface
     */
    // TODO : renommer cette méthode en "addRoute()" ???? et elle prendrait en paramétre directement un objet Route qui est initialisé ???? mais on ferai quoi du basepath dans ce cas ?????
    public function map(string $pattern, RequestHandlerInterface $handler)
    {
        //TODO : lever une exception si on redéclare une route (tester aussi en passant par le group si il est possible de redéclarer une route !!!!) : https://github.com/mastacontrola/AltoRouter/blob/master/AltoRouter.php#L328

        //$route = new Route($this->prefix . $pattern, $target);
        $route = new Route($this->basePath . $pattern, $handler);
        // TODO : on devrait plutot faire un array_unshift($this->routes, $route);  pour que la derniére route soit au début du tableau, dans le cas ou il y a plusieurs route c'est la 1ere trouvée qui gagne, donc autant mettre la derniére le plus haut dans le tableau
        //$this->routes[] = $route;
        // add the route at the top of the array (and not at the end of the array), => in case there is multiple same routes, it's the last route added who will win (in the foreach when searching the first matching route).
        array_unshift($this->routes, $route);
        // TODO : vérifier que cet objet est bien modifiable quand le return est fait, cad qu'il est passé par référence (genre on récupére la route retournée, et si on change la méthode, cela doit être reporté dans l'objet route qui a été ajouté au tableau routes[] précédemment !!!!!).
        return $route;
    }

    /**
     * Check if a route with a specific name exists.
     *
     * @param string $name
     *
     * @return bool true if route exists
     */
    // TODO : regarder ici comment c'est fait : https://github.com/zendframework/zend-router/blob/master/src/SimpleRouteStack.php#L214
    public function hasRoute(string $name): bool
    {
        $routes = array_filter($this->routes, function ($route) use ($name) {
            return $route->getName() === $name;
        });

        return count($routes) > 0;
    }

    /**
     * Get a route by name. First route found win !!!
     *
     * @param string $name
     *
     * @return RouteInterface the route
     */
    // TODO : renommer en getRoute($name) ????
    public function getNamedRoute(string $name)
    {

/*
        $routes = array_filter($this->routes, function ($route) use ($name) {
            return $route->getName() === $name;
        });
        return $routes[0];
*/

        foreach ($this->routes as $route) {
            if ($route->getName() === $name) {
                return $route;
            }
        }
        // TODO : il faudrait pas plutot retourner null si la route n'est pas trouvée plutot qu'une exception ???? si c'est la cas utiliser directement la même méthode que le hasRoute et faire un return au lieu du count !!!!
        throw new RuntimeException('Named route does not exist for name: ' . $name);
    }

    // TODO : ajouter une méthode "removeNamedRoute" ????

    /**
     * Get list of routes.
     *
     * @return RouteInterface[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
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

    /*
     * Delete the cache file
     *
     * @access public
     * @return bool true/false if operation is successfull
     */
    /*
    public function clearCache()
    {
        // Get Filesystem instance
        $fs = new FileSystem;
        // Make sure file exist and delete it
        if ($fs->exists($this->cacheFile)) {
            return $fs->delete($this->cacheFile);
        }
        // It's still considered a success if file doesn't exist
        return true;
    }
    */
}
