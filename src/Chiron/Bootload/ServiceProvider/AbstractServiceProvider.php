<?php

declare(strict_types=1);

namespace Chiron\Bootload\ServiceProvider;

//use Chiron\Http\Middleware\ErrorHandlerMiddleware;
use Chiron\Container\BindingInterface;
use Chiron\Container\Container;

abstract class AbstractServiceProvider implements ServiceProviderInterface
{
    /** @var array */
    protected const BINDINGS = [];
    /** @var array */
    protected const SINGLETONS = [];
    /** @var array */
    protected const ALIASES = [];

    // TODO : réfléchir si on stocke le container dans une variable de classe (cad si on ajoute à cette classe la variable $this->container en protected).
    public function register(BindingInterface $container): void
    {
        $this->registerBindings($container);
        $this->registerSingletons($container);
        $this->registerAliases($container);
    }

    protected function registerBindings(BindingInterface $container): void
    {
        foreach (static::BINDINGS as $key => $value) {
            $key = is_int($key) ? $value : $key;
            $container->bind($key, $value);
        }
    }

    protected function registerSingletons(BindingInterface $container): void
    {
        foreach (static::SINGLETONS as $key => $value) {
            $key = is_int($key) ? $value : $key;
            $container->singleton($key, $value);
        }
    }

    protected function registerAliases(BindingInterface $container): void
    {
        foreach (static::ALIASES as $key => $value) {
            $container->alias($key, $value);
        }
    }
}
