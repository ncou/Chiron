<?php

declare(strict_types=1);

namespace Chiron\Config;

use Nette\Schema\Expect;
use Nette\Schema\Schema;

final class ServicesConfig extends AbstractInjectableConfig
{
    protected const CONFIG_SECTION_NAME = 'services';

    protected function getConfigSchema(): Schema
    {
        return Expect::structure([
            'providers'         => Expect::listOf('string'),
            'bootloaders'       => Expect::listOf('string'),
        ]);
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
