<?php

declare(strict_types=1);

namespace Chiron\Console\Config;

use Chiron\Config\AbstractInjectableConfig;
use Chiron\Config\InjectableInterface;

class ConsoleConfig extends AbstractInjectableConfig implements InjectableInterface
{
    /** @var array */
    protected $config = [
        'commands'   => [],
    ];

    public function inject(array $config): void
    {
        parent::merge($config);
    }

    public function getConfigSection(): string
    {
        return 'console';
    }

    public function getCommands(): array
    {
        return $this->config['commands'];
    }
}
