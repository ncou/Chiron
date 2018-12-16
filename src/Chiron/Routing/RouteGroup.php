<?php

declare(strict_types=1);

namespace Chiron\Routing;

use Chiron\Routing\Traits\MiddlewareAwareInterface;
use Chiron\Routing\Traits\MiddlewareAwareTrait;
use Chiron\Routing\Traits\RouteCollectionInterface;
use Chiron\Routing\Traits\RouteCollectionTrait;
use Chiron\Routing\Traits\RouteConditionHandlerInterface;
use Chiron\Routing\Traits\RouteConditionHandlerTrait;
use Chiron\Routing\Traits\StrategyAwareInterface;
use Chiron\Routing\Traits\StrategyAwareTrait;

class RouteGroup implements MiddlewareAwareInterface, RouteCollectionInterface, RouteConditionHandlerInterface, StrategyAwareInterface
{
    use MiddlewareAwareTrait;
    use RouteCollectionTrait;
    use RouteConditionHandlerTrait;
    use StrategyAwareTrait;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var \RouterInterface
     */
    protected $router;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * Constructor.
     *
     * @param string          $prefix
     * @param callable        $callback
     * @param RouterInterface $router
     */
    public function __construct(string $prefix, callable $callback, RouterInterface $router)
    {
        $this->callback = $callback;
        $this->router = $router;
        $this->prefix = sprintf('/%s', ltrim($prefix, '/'));
    }

    /**
     * Return the prefix of the group.
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function map(string $path, $handler): Route
    {
        $path = ($path === '/') ? $this->prefix : $this->prefix . sprintf('/%s', ltrim($path, '/'));

        $route = $this->router->map($path, $handler);

        $route->setParentGroup($this);

        if ($host = $this->getHost()) {
            $route->setHost($host);
        }
        if ($scheme = $this->getScheme()) {
            $route->setScheme($scheme);
        }
        if ($port = $this->getPort()) {
            $route->setPort($port);
        }

        if (is_null($route->getStrategy()) && ! is_null($this->getStrategy())) {
            $route->setStrategy($this->getStrategy());
        }

        return $route;
    }

    public function group(string $prefix, callable $group): RouteGroup
    {
        $prefix = ($prefix === '/') ? $this->prefix : $this->prefix . sprintf('/%s', ltrim($prefix, '/'));

        return $this->router->group($prefix, $group);
    }

    /**
     * Process the group and ensure routes are added to the collection.
     */
    // TODO : regarder aussi ici : https://github.com/slimphp/Slim/blob/3.x/Slim/RouteGroup.php#L38
    public function __invoke(): void
    {
        // TODO : voir si on fait un bind sur $this
        //call_user_func_array($this->callback->bindTo($this), [$this]);
        ($this->callback)($this);
    }
}
