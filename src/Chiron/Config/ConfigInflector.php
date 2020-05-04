<?php

declare(strict_types=1);

namespace Chiron\Config;

use Chiron\Container\Container;

// TODO : renommer en ConfigInjectableMutation, faire étendre de l'interface MutationInterface, et passer aussi cette classe en final !!!
// TODO : éventuellement ajouter le Trait ContainerAwareInterface et setter automatiquement le container !!!!!
class ConfigInflector
{
    private $container;

    // TODO : éviter de passer un $container en paramétre de la classe ConfigInflector mais lui passer un object ConfigManager !!! Attention pas sur que cela fonctionne car ce configmanager peux être alimenté plus tard par un bootloader, donc les données ne seront peut être pas fraiches entre le moment ou on instancie cette classe et le moment ou on fait le invoke !!!!
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function __invoke(InjectableInterface $injectableConfig)
    {
        $manager = $this->container->get(ConfigManager::class);
        $section = $injectableConfig->getConfigSectionName();

        if ($manager->hasConfig($section)) {
            $config = $manager->getConfig($section);
            // inject in the config the configuration file data.
            $injectableConfig->setConfig($config->toArray());
        }
    }
}
