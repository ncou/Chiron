<?php

declare(strict_types=1);

namespace Chiron\Routing;

use Chiron\Routing\Traits\MiddlewareAwareInterface;
use Chiron\Routing\Traits\MiddlewareAwareTrait;
use Chiron\Routing\Traits\RouteConditionHandlerInterface;
use Chiron\Routing\Traits\RouteCollectionTrait;
use Chiron\Routing\Traits\StrategyAwareInterface;
use Chiron\Routing\Traits\StrategyAwareTrait;
use InvalidArgumentException;
use ArrayIterator;

// TODO : voir si on doit pas ajouyer une méthode pour initialiser la strategy par défaut quand on appel la méthode getIterator() cela éviterai de faire cette alimentation de la strategy de la route si elle est vide dans la classe router.
// TODO : virer la partie middleware de cette classe.
class RouteCollector implements RouteCollectorInterface
{
    use RouteCollectionTrait;

    /**
     * @var \Chiron\Routing\Route[]
     */
    private $routes = [];

    /**
     * @var \Chiron\Routing\RouteGroup[]
     */
    private $groups = [];

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

        // TODO : créer une méthode public "addRoute(RouteInterface $route)" ??????
        //$this->routes[uniqid('UID_', true)] = $route;
        $this->routes[] = $route;

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
     * Get route objects.
     *
     * @return Route[]
     */
    public function getRoutes(): array
    {
        //return array_values($this->toArray());
        //return iterator_to_array($this->getIterator());


        $this->processGroups();

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
    public function removeNamedRoute(string $name): void
    {
        $route = $this->getNamedRoute($name);
        // no exception, route exists, now remove by id
        //unset($this->routes[$route->getIdentifier()]);
        // no exception so far so the route exists we can remove the object safely.
        unset($this->routes[array_search($route, $this->routes)]);
    }

}
