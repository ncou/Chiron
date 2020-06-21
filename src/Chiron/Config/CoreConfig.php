<?php

declare(strict_types=1);

namespace Chiron\Config;

use Nette\Schema\Expect;
use Nette\Schema\Schema;

class CoreConfig extends AbstractInjectableConfig
{
    protected const CONFIG_SECTION_NAME = 'core';

    protected function getConfigSchema(): Schema
    {
        // TODO : virer le otherItem expect mixed !!!!
        return Expect::structure([
            'providers'         => Expect::listOf('string'),
            'bootloaders'       => Expect::listOf('string'),
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
}
