<?php

/**
 * Chiron (http://www.chironframework.com).
 *
 * @see      https://github.com/ncou/Chiron
 *
 * @license   https://github.com/ncou/Chiron/blob/master/licenses/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Chiron\Provider;

use Chiron\Config\Config;
use Chiron\Config\ConfigManager;
use Chiron\Container\Container;
use Chiron\Container\ServiceProvider\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Chiron\Boot\DirectoriesInterface;

// TODO : créer une classe pour fabriquer l'application, et notamment pour injecter les routes et les middlewares si ils sont indiqués sous forme de texte dans la config => https://github.com/zendframework/zend-expressive/blob/85e2f607109ed8608f4004e622b2aad3bcaa8a4d/src/Container/ApplicationConfigInjectionDelegator.php


//https://github.com/Anlamas/beejee/blob/master/src/Core/Config/ConfigServiceProvider.php

/**
 * Config service provider. Should be executed after the dotenv service provider !
 */
class ConfigManagerServiceProvider implements ServiceProviderInterface
{
    /**
     * Register Chiron system services.
     *
     * @param ContainerInterface $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register(Container $container): void
    {
        $settings['app']['settings']['basePath'] = '/';
        $settings['app']['debug'] = false;

        //$config = new Config($settings);
        $config = new ConfigManager();

        $config->merge($settings);

        // register config object
        $container->share(ConfigManager::class, $config);

        /*
                $container->closure(Config::class, function() {
                    $settings['app']['settings']['basePath'] = '/';
                    $settings['app']['debug'] = false;

                    return new Config($settings);
                });*/

        // add alias
        $container->alias('config', ConfigManager::class);

        /*
        $container['config'] = function ($c) {
            return $c->get(Config::class);
        };*/
    }

    public function boot(ConfigManager $config, DirectoriesInterface $directories): void
    {
        $config->loadConfig($directories->get('config'));
    }
}
