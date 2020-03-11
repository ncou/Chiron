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
use Chiron\Bootload\ServiceProvider\ServiceProviderInterface;
use Chiron\Kernel;
use Chiron\Router\FastRoute\FastRouteRouter;
use Chiron\Router\RouteCollector;
use Chiron\Router\RouterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Chiron\Config\ConfigManager;
use Chiron\Container\BindingInterface;
use Chiron\Http\Http;
use Chiron\Http\SapiDispatcher;
use Chiron\Http\DispatcherInterface;
use Chiron\Application;
use Chiron\Console\Console;

/**
 * Chiron system services provider.
 *
 * Registers system services for Chiron, such as config manager, middleware router and dispatcher...
 */
class SharedServiceProvider implements ServiceProviderInterface
{
    /**
     * Register Chiron system services.
     *
     * @param ContainerInterface $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register(BindingInterface $container): void
    {
        // TODO : Il faudra gérer avec la classe "SingletonInterface" pour que le container force la création de l'objet en shared !!!! et ensuite virer cette classe car ce provider ne servira plus à rien !!!!
        //$container->share(RouteCollector::class);

        //$container->share(Http::class);

        // TODO : ajouter un ->share() pour les classes Environement et pour Directories (+ virer leurs interfaces !!!!).

        // TODO : à virer !!!
        //$container->share(DispatcherInterface::class, SapiDispatcher::class);

        $container->share(Application::class);
        $container->share(Console::class);
    }
}
