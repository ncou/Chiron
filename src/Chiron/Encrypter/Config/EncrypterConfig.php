<?php

declare(strict_types=1);

namespace Chiron\Encrypter\Config;

use Chiron\Config\AbstractInjectableConfig;

class EncrypterConfig extends AbstractInjectableConfig
{
    /** @var array */
    protected $config = [
        'key'        => '',
    ];

    public function inject(array $config): void
    {
        parent::merge($config);
    }

    public function getConfigSection(): string
    {
        return 'encrypter';
    }

    public function getKey(): string
    {
        return $this->config['key'];
    }
}
