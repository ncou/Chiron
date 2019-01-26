<?php

declare(strict_types=1);

namespace Chiron\Routing\Resolver;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

// TODO : réfléchir si il ne faut pas plutot merger cette méthode dans la classe Kernel (qui est un container) pour faire un peu comme ici => https://github.com/middlewares/utils/blob/master/src/RequestHandlerContainer.php
// ou aussi un peu dans ce style là => https://github.com/zendframework/zend-expressive/blob/master/src/MiddlewareContainer.php

//https://github.com/isholao/callableresolver/blob/1.x/src/Resolver.php

//https://github.com/catding/WHMCS-7.4.1-decoded/tree/0e6f2d2d6eca9355619a543dd2af1d2cda28fda2/whmcs/vendor/middlewares/utils/src/CallableResolver
//https://github.com/rockefys/Api/tree/ace91d6b68275c0624e5823780da1745d63a57b0/src/SMD/CallableResolver

//https://github.com/saylaco/objects/blob/0ddcf7b9196f237858b3afb5f1bdff98220f1347/src/Attribute/Property/ResolverPropertyType.php
//https://github.com/saylaco/objects/blob/0ddcf7b9196f237858b3afb5f1bdff98220f1347/src/Attribute/Resolver/CallableResolver.php

//https://github.com/vixxjy/whmcs/blob/92caef678d7268ed9a15e018b4639b62ef20642c/vendor/middlewares/utils/src/CallableResolver/ReflectionResolver.php

//https://github.com/lambirou/babiphp/blob/master/system/Container/ReflectionContainer.php

//https://github.com/silexphp/Silex/blob/master/src/Silex/CallbackResolver.php

/**
 * This class resolves a ControllerName of the format 'class@method' into a callable that can be invoked.
 */
final class ControllerResolver implements ControllerResolverInterface
{
    public const PATTERN = '~^([^@]+)@([^@]+)$~';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface|null $container
     */
    // TODO : virer ce constructeur et faire étendre cette classe du Trait "ContainerAware" et "ContainerAwareInterface"
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Resolve toResolve into a callable that that the router can dispatch.
     *
     * If toResolve is of the format 'class@method', then try to extract 'class'
     * from the container otherwise instantiate it and then dispatch 'method'.
     *
     * @param callable|string $toResolve
     *
     * @throws \RuntimeException         if the callable does not exist
     * @throws \InvalidArgumentException if the callable is not resolvable
     *
     * @return callable
     */
    public function resolve($toResolve): callable
    {
        $resolved = $toResolve;

        if (! is_callable($toResolve) && is_string($toResolve)) {
            $class = $toResolve;
            $method = '__invoke';

            // check for chiron callable as "class@method"
            if (preg_match(static::PATTERN, $toResolve, $matches)) {
                $class = $matches[1];
                $method = $matches[2];
            }
            // check if the class is present un the container
            if ($this->container instanceof ContainerInterface && $this->container->has($class)) {
                $class = $this->container->get($class);
            } else {
                // if not present, try to instantitate it with the autoloader
                if (! class_exists($class)) {
                    throw new \RuntimeException(sprintf('Callable "%s" does not exist', $class));
                }
                // do not instantiate the classe when you use the magic method for generic static methods.
                // TODO : regarder si il est possible d'améliorer le code comme ca => https://github.com/middlewares/utils/blob/master/src/RequestHandlerContainer.php#L84
                // TODO : ou comme ici => https://github.com/PHP-DI/Invoker/blob/master/src/CallableResolver.php#L122
                if (! method_exists($class, '__callStatic')) {
                    $class = new $class();
                }
            }

            // For a class that implements RequestHandlerInterface, we will call the handle() method.
            if ($class instanceof RequestHandlerInterface) {
                $method = 'handle';
            }

            $resolved = [$class, $method];
        }

        if (! is_callable($resolved)) {
            throw new \InvalidArgumentException(sprintf(
                '(%s) is not resolvable.',
                is_array($toResolve) || is_object($toResolve) || is_null($toResolve) ? json_encode($toResolve) : $toResolve
            ));
        }

        return $resolved;
    }
}
