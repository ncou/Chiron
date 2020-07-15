<?php

declare(strict_types=1);

namespace Chiron\Config;

use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Chiron\Dispatcher\ConsoleDispatcher;
use Chiron\Dispatcher\SapiDispatcher;
use Chiron\Dispatcher\RrDispatcher;

class AppConfig extends AbstractInjectableConfig
{
    protected const CONFIG_SECTION_NAME = 'app';

    protected function getConfigSchema(): Schema
    {
        // TODO : on ne devrait pas pouvoir stocker de dispatcher dans le fichier app.php, car c'est plutot défini dans core.php. Par contre il mnaque la partie "commands" pour la console !!!!!
        // TODO : virer le otherItem expect mixed !!!!
        return Expect::structure([
            'dispatchers'       => Expect::listOf('string')->default([ConsoleDispatcher::class, SapiDispatcher::class, RrDispatcher::class,]),
            'providers'         => Expect::listOf('string'),
            'bootloaders'       => Expect::listOf('string'),
        ])->otherItems(Expect::mixed());
    }

    public function getDispatchers(): array
    {
        return $this->get('dispatchers');
    }

    public function getProviders(): array
    {
        return $this->get('providers');
    }

    public function getBootloaders(): array
    {
        return $this->get('bootloaders');
    }
}
