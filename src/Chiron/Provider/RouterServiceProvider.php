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
use Chiron\Routing\Router;
use Chiron\Routing\RouterInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Chiron\Http\Factory\ResponseFactory;
use Chiron\Routing\Resolver\CallableResolver;
use Chiron\Routing\Strategy\ApplicationStrategy;
use Chiron\KernelInterface;

/**
 * Chiron system services provider.
 *
 * Registers system services for Chiron, such as config manager, middleware router and dispatcher...
 */
class RouterServiceProvider extends ServiceProvider
{
    /**
     * Register Chiron system services.
     *
     * @param ContainerInterface $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register(KernelInterface $kernel): void
    {
        // register router object
        $kernel[RouterInterface::class] = function ($c) {
            $router = new Router();

            $router->setBasePath($c->config['app.settings.basePath'] ?? '/');

            $router->setStrategy(new ApplicationStrategy(new ResponseFactory(), new CallableResolver($c)));

            return $router;
        };

        // add alias
        $kernel['router'] = function ($c) {
            return $c->get(RouterInterface::class);
        };
    }
}
