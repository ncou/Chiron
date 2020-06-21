<?php

declare(strict_types=1);

namespace Chiron\Config;

use Chiron\Container\Container;
use Chiron\Facade\Configure;

// TODO : déplacer cette classe dans un répertoire "Container\Mutation\" pour y stocker toutes les mutations utilisées par le container
final class InjectableConfigMutation
{
    public static function mutation(InjectableConfigInterface $config)
    {
        $section = $config->getConfigSectionName();

        if (Configure::hasConfig($section)) {
            // the section subset could be empty.
            $subset = $config->getSectionSubsetName();
            // get the data array for section and subset-section.
            $data = Configure::getConfigData($section, $subset);
            // inject in the config the configuration file data.
            $config->setData($data);
        }
    }
}
