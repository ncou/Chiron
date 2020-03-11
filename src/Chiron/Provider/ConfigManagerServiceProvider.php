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
use Chiron\Bootload\ServiceProvider\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Chiron\Boot\DirectoriesInterface;
use Chiron\Invoker\Support\Invokable;
use Chiron\Container\BindingInterface;
use Closure;


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
    public function register(BindingInterface $container): void
    {
        // register object
        $container->share(ConfigManager::class, new Invokable(Closure::fromCallable([$this, 'configManager'])));
        //$container->share(ConfigManager::class, new Invokable([ConfigManagerServiceProvider::class, 'configManager']));
        //$container->share(ConfigManager::class, new Invokable([self::class, 'configManager']));
        //$container->share(ConfigManager::class, new Invokable([$this, 'configManager']));

        // add alias
        $container->alias('config', ConfigManager::class);
    }

    private function configManager(DirectoriesInterface $directories): ConfigManager {

        $config = new ConfigManager();

        //if ($this->runningInConsole()) {
        //    $this->basePath = getcwd();
        //} else {
        //    $this->basePath = realpath(getcwd().'/../');
        //}


        // init the default values
        $config->loadConfig(__DIR__.'/../../../config/');

        // read the config files in the config folder
        $config->loadConfig($directories->get('config'));

        // TODO : il fa falloir ajouter une méthode default dans la classe de config !!!!
        //$settings['templates']['paths'] = [$directories->get('templates')];
        //$config->merge($settings);

        return $config;
    }
}
