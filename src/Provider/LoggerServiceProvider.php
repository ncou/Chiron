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

//https://github.com/yiisoft/yii-demo/blob/b8eca58c301b2326908362ae4f7801a7357c3a56/src/Provider/LoggerProvider.php

//https://github.com/mouyong/foundation-sdk/blob/c40068866c6fe9c7359f3f85b754190d1e3f311e/src/Log.php#L28

declare(strict_types=1);

namespace Chiron\Provider;

use Chiron\Core\Container\Provider\ServiceProviderInterface;
use Chiron\Container\BindingInterface;
use Chiron\Container\Container;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

//use Monolog\Logger;
//use Monolog\Handler\StreamHandler;

//https://github.com/spiral/monolog-bridge/blob/master/src/Bootloader/MonologBootloader.php

//https://github.com/browscap/browscap-php/blob/5f1436b74a100088c66423e8715de598f50e66d1/src/Helper/LoggerHelper.php
//https://github.com/browscap/http-access-log-parser/blob/f263af3d9e87b7f93ad89192f1ee2395ee023187/src/Helper/LoggerHelper.php
//https://github.com/reactive-apps/app/blob/master/config/di/logger.php

// TODO : créer un Logger par défaut minimaliste et ne pas utiliser le NullLogger !!!!! => https://github.com/slimphp/Slim/blob/1df2f0d78589f1a7b5199c873ab1e7ec57fe3e0a/Slim/Logger.php

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
    public function register(BindingInterface $container): void
    {
        // TODO : créer un Logger par défaut minimaliste et ne pas utiliser le NullLogger !!!!! => https://github.com/slimphp/Slim/blob/1df2f0d78589f1a7b5199c873ab1e7ec57fe3e0a/Slim/Logger.php
        $container->bind(LoggerInterface::class, NullLogger::class);

        // add alias
        //$container->alias('logger', LoggerInterface::class);
        //$container->alias('log', LoggerInterface::class);
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

    /**
     * creates a \Monolog\Logger instance.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return LoggerInterface
     */
    //https://github.com/medeirosinacio/yii2-website-template/blob/74797c873ac262c14e4bfee7292ddbfc827a9d90/vendor/browscap/browscap-php/src/Helper/LoggerHelper.php#L31
    public static function createDefaultLogger(OutputInterface $output): LoggerInterface
    {
        $logger = new Logger('browscap');
        $consoleLogger = new ConsoleLogger($output);
        $psrHandler = new PsrHandler($consoleLogger);

        $logger->pushHandler($psrHandler);

        /** @var callable $memoryProcessor */
        $memoryProcessor = new MemoryUsageProcessor(true);
        $logger->pushProcessor($memoryProcessor);

        /** @var callable $peakMemoryProcessor */
        $peakMemoryProcessor = new MemoryPeakUsageProcessor(true);
        $logger->pushProcessor($peakMemoryProcessor);

        ErrorHandler::register($logger);

        return $logger;
    }
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
