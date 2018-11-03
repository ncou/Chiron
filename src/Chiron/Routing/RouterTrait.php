<?php

declare(strict_types=1);

namespace Chiron\Routing;

use Chiron\Handler\DeferredRequestHandler;
use Psr\Http\Server\RequestHandlerInterface;
use Closure;
use InvalidArgumentException;


trait RouterTrait
{
    use RoutableTrait;

    /** @var RouteCollectionInterface */
    private $routeCollector = null;

    /**
     * Add route with multiple methods.
     *
     * @param string                                  $pattern The route URI pattern
     * @param RequestHandlerInterface|callable|string $handler The route callback routine
     *
     * @return \Chiron\Routing\Route
     */
    // TODO : créer une classe RouteInterface qui servira comme type de retour (il faudra aussi l'ajouter dans le use en début de classe) !!!!!
    // TODO : lever une exception si le type du handler n'est pas correct, par exemple si on lui passe un integer ou un objet non callable !!!!!
    public function map(string $pattern, $handler): Route
    {
        if (is_string($handler) || is_callable($handler)) {
            $handler = new DeferredRequestHandler($handler, $this->container);
        }

        if (! $handler instanceof RequestHandlerInterface) {
            throw new InvalidArgumentException('Handler should be a Psr\Http\Server\RequestHandlerInterface instance');
        }

        //return $this->getRouter()->map($pattern, $handler);
        return $this->getRouteCollector()->map($pattern, $handler);
    }

    // $params => string|array
    // TODO : renommer $closure en $group
    public function group(string $prefix, Closure $closure): RouteGroup
    {
        /*
        $group = new RouteGroup($prefix, $this->getRouter(), $this->getContainer());
        // TODO : on fait un bind du this avec le group ????
        //$closure = $closure->bindTo($group);
        call_user_func($closure, $group);
        // TODO : un return de type $group est à utiliser si on veux ajouter un middleware avec la notation : $app->group(xxxx, xxxxx)->middleware(xxx);
        return $group;
        */
        $routeGroup = $this->getRouteCollector()->group($prefix, $closure);

        return $routeGroup;
    }

    /**
     * {@inheritdoc}
     *
     * @see \BitFrame\Router\RouterInterface::getRoutes()
     */
    public function getRoutes(): array
    {
        return ($this->getRouteCollector())->getRoutes();
    }

    /**
     * Get the RouteCollection object.
     *
     * @return RouteCollectionInterface
     */
    public function getRouteCollector()//: RouteCollectorInterface
    {
        return $this->routeCollector;
    }
}
