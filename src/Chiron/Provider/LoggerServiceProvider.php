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

use Chiron\Container\Container;
use Chiron\Routing\Router;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Chiron\Container\ServiceProvider\ServiceProviderInterface;

/**
 * Chiron system services provider.
 *
 * Registers system services for Chiron, such as config manager, middleware router and dispatcher...
 */
class LoggerServiceProvider implements ServiceProviderInterface
{
    /**
     * Register Chiron system services.
     *
     * @param ContainerInterface $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register(Container $container): void
    {
        // TODO : initialiser un logger ici ???? et éventuellement créer une propriété pour changer le formater dans la restitution de la log. cf nanologger et la liste des todo pour mettre un formater custom à passer en paramétre du constructeur !!!!

        // register router object
        $container->add(LoggerInterface::class, function () {
            return new NullLogger();
            //$logger = new NullLogger();
            // TODO : à améliorer !!!! regarder la notion de daily et single et de log_max_files : https://laravel.com/docs/5.2/errors

            // TODO : rajouter le composant logger dans le fichier composer.json et ensuite décommenter cette ligne !!!!
            //$app->setLogger(new Logger(Chiron\ROOT_DIR.Chiron\DS.Chiron\LOG_DIR_NAME.Chiron\DS.'CHIRON.log'));
        });

        // add alias
        $container->alias('logger', LoggerInterface::class);

        /*
        $kernel['logger'] = function ($c) {
            return $c->get(LoggerInterface::class);
        };*/
    }
}
