<?php

declare(strict_types=1);

namespace Chiron\Encrypter\Config;

use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Chiron\Config\AbstractInjectableConfig;

final class EncrypterConfig extends AbstractInjectableConfig
{
    protected const CONFIG_SECTION_NAME = 'encrypter';

    protected function getConfigSchema(): Schema
    {
        return Expect::structure([
            'key' => Expect::string()->default(env('APP_KEY'))
        ]);
    }

    public function getKey(): string
    {
        return $this->get('key');
    }
}
