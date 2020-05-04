<?php

declare(strict_types=1);

namespace Chiron\Provider;

use Chiron\Boot\DirectoriesInterface;
use Chiron\Bootload\ServiceProvider\ServiceProviderInterface;
use Chiron\Config\Config;
use Chiron\Config\ConfigManager;
use Chiron\Container\BindingInterface;
use Chiron\Container\Container;
use Chiron\Invoker\Support\Invokable2;
use Closure;
use Psr\Container\ContainerInterface;


use Chiron\Invoker\CallableResolver;

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

/*
        $callableResolver = new CallableResolver($container);
        //$callable = $callableResolver->resolve_SAVE([$this, 'configManager']);
        $callable = $callableResolver->resolve_SAVE([$this, 'configManager']);

        die(var_dump(is_callable($callable)));
*/

        $container->share(ConfigManager::class, new Invokable2([$this, 'configManager']));

        //$container->share(ConfigManager::class, new Invokable(Closure::fromCallable([$this, 'configManager'])));




        // register object
        //$container->share(ConfigManager::class, new Invokable(Closure::fromCallable([$this, 'configManager'])));
        //$container->share(ConfigManager::class, new Invokable([ConfigManagerServiceProvider::class, 'configManager']));
        //$container->share(ConfigManager::class, new Invokable([self::class, 'configManager']));
        //$container->share(ConfigManager::class, new Invokable([$this, 'configManager']));

        // add alias
        $container->alias('config', ConfigManager::class);
    }

    // TODO : sépararer la création de la classe ConfigManager, et l'initialisation de la classe (les loadfromdirectory()) qui devra être mise dans une classe séparée de type Bootloader !!!!
    private static function configManager(DirectoriesInterface $directories): ConfigManager
    {
        // TODO : permettre de passer au constructeur du ConfigManager un path ou un tableau de path pour charger les configurations. Ca éviterai un appel à la méthode loadFromDirectory() car le path serait donné directement au constructeur !!!!
        $manager = new ConfigManager();

        //if ($this->runningInConsole()) {
        //    $this->basePath = getcwd();
        //} else {
        //    $this->basePath = realpath(getcwd().'/../');
        //}

        // init the default values
        $manager->loadFromDirectory(__DIR__ . '/../../../config/');

        // read the config files in the config folder
        $manager->loadFromDirectory($directories->get('config'));

        // TODO : il fa falloir ajouter une méthode default dans la classe de config !!!!
        //$settings['templates']['paths'] = [$directories->get('templates')];
        //$config->merge($settings);

        return $manager;
    }
}
