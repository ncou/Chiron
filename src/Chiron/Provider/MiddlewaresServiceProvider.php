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

use Chiron\Pipe\Pipeline;
use Chiron\Http\Middleware\BodyParserMiddleware;
use Chiron\Http\Middleware\CharsetByDefaultMiddleware;
use Chiron\Http\Middleware\CheckMaintenanceMiddleware;
use Chiron\Http\Middleware\ContentLengthMiddleware;
use Chiron\Http\Middleware\ContentTypeByDefaultMiddleware;
use Chiron\Http\Middleware\DispatcherMiddleware;
use Chiron\Http\Middleware\EmitterMiddleware;
//use Chiron\Http\Middleware\ErrorHandlerMiddleware;
use Chiron\Http\Middleware\MethodOverrideMiddleware;
use Chiron\Http\Middleware\OriginalRequestMiddleware;
use Chiron\Http\Middleware\RoutingMiddleware;
use Chiron\Routing\Router;
use Psr\Container\ContainerInterface;

/**
 * Chiron system services provider.
 *
 * Registers system services for Chiron, such as config manager, middleware router and dispatcher...
 */
class MiddlewaresServiceProvider
{
    /**
     * Register Chiron system services.
     *
     * @param ContainerInterface $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register(ContainerInterface $container)
    {
        $container[RoutingMiddleware::class] = function ($c) {
            return new RoutingMiddleware($c[Router::class]);
        };

        $container[DispatcherMiddleware::class] = function ($c) {
            return new DispatcherMiddleware(new Pipeline($c));
        };

        $container[ContentTypeByDefaultMiddleware::class] = function ($c) {
            return new ContentTypeByDefaultMiddleware();
        };
        $container[OriginalRequestMiddleware::class] = function ($c) {
            return new OriginalRequestMiddleware();
        };

        $container[CharsetByDefaultMiddleware::class] = function ($c) {
            return new CharsetByDefaultMiddleware();
        };

        $container[BodyParserMiddleware::class] = function ($c) {
            return new BodyParserMiddleware();
        };

        /*
                $container[EmitterMiddleware::class] = function ($c) {
                    return new EmitterMiddleware();
                };
        */

        $container[CheckMaintenanceMiddleware::class] = function ($c) {
            return new CheckMaintenanceMiddleware();
        };

        $container[MethodOverrideMiddleware::class] = function ($c) {
            return new MethodOverrideMiddleware();
        };

        $container[ContentLengthMiddleware::class] = function ($c) {
            return new ContentLengthMiddleware();
        };

        /*
           $container['callableResolver'] = function ($container) {
               return new CallableResolver($container);
           };
        */

        //$this->factory = new MiddlewareFactory($this->container);

        //$this->loadConfig($config_path_or_file_or_array, $config_cache_file);

        // TODO : déplacer ces initialisations dans le constructeur d'une classe CONTAINER externalisée

        // TODO : ajouter l'initialisation d'un logger ?????

        // TODO : vérifier l'utilité de mettre cela dans un container, normalement on va toujours passer par le router, donc le mettre dans un container n'est pas vraiment nécessaire, surtout que dans les controller on ne va pas réutiliser le router, car la méthode redirect ou getPathFor se trouve directement dans $app et pas dans la classe Router.
        // register the router in the pimple container

        /*
            $this['session'] = function ($c) {
                // TODO : déplacer la classe session dans le répertoire "components"
                return new Session();
            };
        */

        /*
            $this['router'] = function ($c) {
                return new Router($c->get('basePath'), $this->container);
            };
        */

        // Create request class closure.
        /*
            $this['request'] = function ($c) {
                return Request::fromGlobals();
            };
        */

        // -----------------------------------------------------------------------------
        // Service providers
        // -----------------------------------------------------------------------------
        // Twig
        // TODO : s'inspirer de ce bout de code pour passer des variables global directement à phpRenderer
        /*
        $view = new \Slim\Views\Twig(
            $app->settings['view']['template_path'],
            $app->settings['view']['twig']
        );
        $view->addExtension(new Twig_Extension_Debug());
        $view->addExtension(new \Slim\Views\TwigExtension($app->router, $app->request->getUri()));
        */
        /* @var \Twig_Environment $env */
        /*
        $env = $view->getEnvironment();
        foreach ($app->settings['view']['globals'] as $global => $value) {
            $env->addGlobal($global, $value);
        }
        $container->register($view);*/

        // LOAD Referral Spammer List
        //$spammerList = config('app.referral_spam_list_location', base_path('vendor/matomo/referrer-spam-blacklist/spammers.txt'));
    }
}
