<?php

declare(strict_types=1);

namespace Chiron\Config;

use Chiron\Config\AbstractInjectableConfig;
use Chiron\Framework;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

// TODO : à déplacer dans le package chiron/chiron ????
final class ConsoleConfig extends AbstractInjectableConfig
{
    protected const CONFIG_SECTION_NAME = 'console';

    protected function getConfigSchema(): Schema
    {
        // TODO : il faudrait plutot utiliser un Expect::listOf('string') car ce n'est pas un tableau associatif
        // TODO : attention on va être embété avec les dépendances car la classe Framework est dans le composant chiron/chiron il faudrait le déplacer dans le composer chiron/core pour plus de souplesse et limiter les dépendance au package core uniquement !!!
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
