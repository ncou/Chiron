<?php

declare(strict_types=1);

namespace Chiron\Config;

use Chiron\Container\Container;
use Chiron\Config\InjectableInterface;
use Chiron\Config\ConfigManager;

class ConfigInflector
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function __invoke(InjectableInterface $config)
    {
        $configManager = $this->container->get(ConfigManager::class);

        // handle the case when the user use a directory separator (windows ou linux value) in the linked file path
        $section = str_replace(array('/', '\\'),'.', $config->getLinkedFile());

        $data = $configManager->getConfig($section);

        // inject in the config object, the array settings found in the configuration file (using the configManager to get the data).
        $config->merge($data);

    }

/*
    function constant_exists($class, $name){
        if(is_string($class)){
            return defined("$class::$name");
        } else if(is_object($class)){
            return defined(get_class($class)."::$name");
        }
        return false;
    }
*/

}
