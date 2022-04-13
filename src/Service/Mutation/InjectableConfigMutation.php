<?php

declare(strict_types=1);

namespace Chiron\Service\Mutation;

use Chiron\Config\InjectableConfigInterface;
use Chiron\Container\Container;
use Chiron\Config\Configure;

final class InjectableConfigMutation
{
    public static function mutation(InjectableConfigInterface $config): void
    {
        $section = $config->getConfigSectionName();

        $configure = (Container::$instance)->get(Configure::class);

        // TODO : il se passe quoi si le subset n'est pas valide ??? une exception est levée ????

        // TODO : utiliser directement la fonction 'configure($section, $subset)' et si la réponse n'est pas null alors faire un setData !!!!
        if ($configure->exists($section)) {
            // the section subset could be empty.
            $subset = $config->getSectionSubsetName();
            // get the data array for section and subset-section.
            $data = $configure->read($section, $subset); // TODO : il faut gérer le subset dans la classe Configure::class !!!!

            // inject in the config the configuration file data.
            // TODO : il faudra peut etre faire un try/catch et transformer l'exception en ApplicationException ou ConfiguratorException. Je pense que si on injecte des mauvaises données on aura une exception car le schema sera invalid !!!!
            $config->setData($data);
        }
    }
}
