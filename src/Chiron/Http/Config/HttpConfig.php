<?php

declare(strict_types=1);

namespace Chiron\Http\Config;

use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Chiron\Config\AbstractInjectableConfig;

class HttpConfig extends AbstractInjectableConfig
{
    protected const CONFIG_SECTION_NAME = 'http';

    // TODO : il faudra surement améliorer le Expect::array pour le header histoire de sécuriser le contenu de ce tableau !!!!
    protected function getConfigSchema(): Schema
    {
        return Expect::structure([
            'bufferSize'        => Expect::int()->default(8 * 1024 * 1024),
            'protocol'          => Expect::string()->default('1.1'),
            'basePath'          => Expect::string()->default('/'),
            'headers'           => Expect::array(),
            'middlewares'       => Expect::arrayOf('string'),
        ]);
    }

    public function getBufferSize(): int
    {
        return $this->get('bufferSize');
    }

    public function getProtocol(): string
    {
        return $this->get('protocol');
    }

    public function getBasePath(): string
    {
        return $this->get('basePath');
    }

    public function getHeaders(): array
    {
        return $this->get('headers');
    }

    public function getMiddlewares(): array
    {
        return $this->get('middlewares');
    }
}
