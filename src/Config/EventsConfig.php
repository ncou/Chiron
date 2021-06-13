<?php

declare(strict_types=1);

namespace Chiron\Config;

use Nette\Schema\Expect;
use Nette\Schema\Schema;

// TODO : classe à finir de coder !!!
final class EventsConfig extends AbstractInjectableConfig
{
    protected const CONFIG_SECTION_NAME = 'events';

    protected function getConfigSchema(): Schema
    {
        return Expect::structure([
            'listeners' => Expect::listOf('string')
        ]);
    }

    public function getListeners(): array
    {
        return $this->get('listeners');
    }
}
