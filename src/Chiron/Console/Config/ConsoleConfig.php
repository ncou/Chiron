<?php

declare(strict_types=1);

namespace Chiron\Console\Config;

use Chiron\Config\AbstractInjectableConfig;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

class ConsoleConfig extends AbstractInjectableConfig
{
    protected const CONFIG_SECTION_NAME = 'console';

    protected function getConfigSchema(): Schema
    {
        // TODO : il faudrait plutot utiliser un Expect::listOf('string') car ce n'est pas un tableau associatif
        return Expect::structure(['commands' => Expect::arrayOf('string')]);
    }

    public function getCommands(): array
    {
        return $this->get('commands');
    }
}
