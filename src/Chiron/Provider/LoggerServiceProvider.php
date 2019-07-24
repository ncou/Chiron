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
use Chiron\Container\ServiceProvider\ServiceProviderInterface;
use Chiron\Routing\Router;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

//use Monolog\Logger;
//use Monolog\Handler\StreamHandler;

//https://github.com/spiral/monolog-bridge/blob/master/src/Bootloader/MonologBootloader.php

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

        $container->add(LoggerInterface::class, function () {
            return new NullLogger();
        });

        // add alias
        $container->alias('logger', LoggerInterface::class);
        $container->alias('log', LoggerInterface::class);
    }

/*
    public function register()
    {
        $this->app->singleton('log', function () {
            $logger = new Logger('slayer');
            $logger_name = 'slayer';
            if ($ext = logging_extension()) {
                $logger_name .= '-'.$ext;
            }
            $logger->pushHandler(
                new StreamHandler(
                    storage_path('logs').'/'.$logger_name.'.log',
                    Logger::DEBUG
                )
            );
            return $logger;
        });
    }*/
}


//if (! function_exists('logging_extension')) {
    /**
     * This returns an extension name based on the requested logging time.
     *
     * @return string
     */
    /*
    function logging_extension()
    {
        $ext = '';
        switch ($logging_time = config()->app->logging_time) {
            case 'hourly':
                $ext = date('Y-m-d H-00-00');
            break;
            case 'daily':
                $ext = date('Y-m-d 00-00-00');
            break;
            case 'monthly':
                $ext = date('Y-m-0 00-00-00');
            break;
            case '':
            case null:
            case false:
                return $ext;
            break;
            default:
                throw new Exception('Logging time['.$logging_time.'] not found');
            break;
        }
        return $ext;
    }*/
//}
