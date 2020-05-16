<?php

declare(strict_types=1);

namespace Chiron\Config;

use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Chiron\Config\AbstractInjectableConfig;

class AppConfig extends AbstractInjectableConfig
{
    protected const CONFIG_SECTION_NAME = 'app';

    protected function getConfigSchema(): Schema
    {
        // TODO : virer le otherItem expect mixed !!!!
        return Expect::structure([
            'dispatchers'       => Expect::listOf('string'),
            'providers'       => Expect::listOf('string'),
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
