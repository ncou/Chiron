<?php

declare(strict_types=1);

namespace Chiron\Config;

use Nette\Schema\Expect;
use Nette\Schema\Schema;

// TODO : ajouter des commentaires dans le fichier app. exemple : https://github.com/codeigniter4/CodeIgniter4/blob/8da88e04ae151ac6b06da431fb93ca086559b565/app/Config/App.php

final class AppConfig extends AbstractInjectableConfig
{
    protected const CONFIG_SECTION_NAME = 'app';

    // TODO : ajouter un nom ("name") pour l'application ???? exemple lorsqu'on affichera dans la console la version de l'application. Potentiellement stocker cette valeur dans la classe SettingsConfig

    protected function getConfigSchema(): Schema
    {
        // TODO : on ne devrait pas stocker de dispatcher dans le fichier app.php (ca devrait être un tableau vide), car c'est plutot défini dans core.php. Par contre il mnaque la partie "commands" pour la console !!!!!
        // TODO : virer le otherItem expect mixed !!!!
        return Expect::structure([
            'providers'         => Expect::listOf('string'),
            'bootloaders'       => Expect::listOf('string'),
            'dispatchers'       => Expect::listOf('string'),
        ])->otherItems(Expect::mixed());
    }

    public function getProviders(): array
    {
        return $this->get('providers');
    }

    public function getBootloaders(): array
    {
        return $this->get('bootloaders');
    }

    public function getDispatchers(): array
    {
        return $this->get('dispatchers');
    }
}
