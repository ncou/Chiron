<?php

declare(strict_types=1);

namespace Chiron\Console\Config;

use Chiron\Config\AbstractInjectableConfig;
use Chiron\Framework;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

final class ConsoleConfig extends AbstractInjectableConfig
{
    protected const CONFIG_SECTION_NAME = 'console';

    protected function getConfigSchema(): Schema
    {
        // TODO : il faudrait plutot utiliser un Expect::listOf('string') car ce n'est pas un tableau associatif
        return Expect::structure([
            'name'     => Expect::string()->default(Framework::banner()),
            'version'  => Expect::string()->default(Framework::version()),
            'commands' => Expect::arrayOf('string'),
        ]);
    }

    public function getCommands(): array
    {
        return $this->get('commands');
    }

    public function getName(): string
    {
        //return $this->get('name') ?? 'UNKNOWN';
        return $this->get('name');
    }

    public function getVersion(): string
    {
        //return $this->get('version') ?? 'UNKNOWN';
        return $this->get('version');
    }
}
