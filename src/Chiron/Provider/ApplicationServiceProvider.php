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
use Chiron\Routing\Strategy\CallableResolver;
use Chiron\Routing\Strategy\ApplicationStrategy;
use Chiron\KernelInterface;

/**
 * Chiron system services provider.
 *
 * Registers system services for Chiron, such as config manager, middleware router and dispatcher...
 */
class ApplicationServiceProvider extends ServiceProvider
{
    /**
     * Register Chiron system services.
     *
     * @param ContainerInterface $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register(KernelInterface $kernel): void
    {
        // TODO : initialiser un logger ici ???? et éventuellement créer une propriété pour changer le formater dans la restitution de la log. cf nanologger et la liste des todo pour mettre un formater custom à passer en paramétre du constructeur !!!!

        $kernel[RouterInterface::class] = function ($c) {
            $router = new Router();

            $router->setBasePath($c->config['app.settings.basePath'] ?? '/');

            $router->setStrategy(new ApplicationStrategy(new ResponseFactory(), new CallableResolver($c)));

            return $router;
        };

        $kernel[LoggerInterface::class] = function ($c) {
            return new NullLogger();
            //$logger = new NullLogger();
            // TODO : à améliorer !!!! regarder la notion de daily et single et de log_max_files : https://laravel.com/docs/5.2/errors

            // TODO : rajouter le composant logger dans le fichier composer.json et ensuite décommenter cette ligne !!!!
            //$app->setLogger(new Logger(Chiron\ROOT_DIR.Chiron\DS.Chiron\LOG_DIR_NAME.Chiron\DS.'CHIRON.log'));
        };
    }
}
