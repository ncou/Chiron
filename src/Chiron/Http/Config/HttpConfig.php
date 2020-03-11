<?php

declare(strict_types=1);

namespace Chiron\Http\Config;

use Chiron\Config\AbstractInjectableConfig;
use Chiron\Config\InjectableInterface;

class HttpConfig extends AbstractInjectableConfig implements InjectableInterface
{
    /** @var array */
    protected $config = [
        'bufferSize'        => 8 * 1024 * 1024,
        'protocol'          => '1.1',
        'basePath'          => '/',
        'headers'           => ['Content-Type' => 'UTF8'],
        'middlewares'       => [],
    ];

    public function inject(array $config): void
    {
        parent::merge($config);
    }

    public function getConfigSection(): string
    {
        return 'http';
    }

    public function getBufferSize(): int
    {
        return $this->config['bufferSize'];
    }

    public function getProtocol(): string
    {
        return $this->config['protocol'];
    }

    public function getDefaultHeaders(): array
    {
        return (array) $this->config['headers'];
    }

    public function getBasePath(): string
    {
        return $this->config['basePath'];
    }

    public function getMiddlewares(): array
    {
        return (array) $this->config['middlewares'];
    }
}
