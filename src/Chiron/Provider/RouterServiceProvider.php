<?php

/**
 * Chiron (http://www.chironframework.com).
 *
 * @see      https://github.com/ncou/Chiron
 *
 * @license   https://github.com/ncou/Chiron/blob/master/licenses/LICENSE.md (MIT License)
 */

//https://github.com/userfrosting/UserFrosting/blob/master/app/system/ServicesProvider.php
//https://github.com/slimphp/Slim/blob/3.x/Slim/DefaultServicesProvider.php
declare(strict_types=1);

namespace Chiron\Provider;

//use Chiron\Http\Middleware\ErrorHandlerMiddleware;
use Chiron\Container\Container;
use Chiron\Container\InvokerInterface;
use Chiron\Container\ServiceProvider\ServiceProviderInterface;
use Chiron\Kernel;
use Chiron\Routing\Resolver\ControllerResolver;
use Chiron\Routing\Router;
use Chiron\Routing\RouteCollector;
use Chiron\Routing\RouterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

/**
 * Chiron system services provider.
 *
 * Registers system services for Chiron, such as config manager, middleware router and dispatcher...
 */
class RouterServiceProvider implements ServiceProviderInterface
{
    /**
     * Register Chiron system services.
     *
     * @param ContainerInterface $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register(Container $container): void
    {
        // register router object
        /*
        $container[RouterInterface::class] = function ($c) {
            $router = new Router();

            $router->setBasePath($c->config['app.settings.basePath'] ?? '/');

            // TODO : aller chercher la responsefactory directement dans le container plutot que de faire un new ResponseFactory !!!!
            // TODO : aller chercher la controllerResolver directement dans le container plutot que de faire un new !!!! ca permettra de faire un override de cette classe si l'utilisateur souhaite redéfinir le resolver.
            $router->setStrategy(new HtmlStrategy(new ResponseFactory(), new ControllerResolver($c)));

            return $router;
        };*/

        $container->share(RouterInterface::class, function () use ($container) {
            $router = new Router();

            //$router->setBasePath($kernel->getConfig()['app.settings.basePath'] ?? '/');

            // TODO : aller chercher la responsefactory directement dans le container plutot que de faire un new ResponseFactory !!!!
            // TODO : aller chercher la controllerResolver directement dans le container plutot que de faire un new !!!! ca permettra de faire un override de cette classe si l'utilisateur souhaite redéfinir le resolver.
            //$router->setStrategy(new HtmlStrategy($container->get(ResponseFactoryInterface::class), $container->get(InvokerInterface::class)));

            //$collector = new RouteCollector();
            //$router->setRouteCollector($collector);

            return $router;
        });

        // add alias
        $container->alias('router', RouterInterface::class);

        /*
        $container['router'] = function ($c) {
            return $c->get(RouterInterface::class);
        };*/
    }
}
