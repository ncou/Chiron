<?php

declare(strict_types=1);

namespace Chiron\Config;

use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Chiron\Dispatcher\ConsoleDispatcher;
use Chiron\Dispatcher\SapiDispatcher;
use Chiron\Dispatcher\RrDispatcher;
use Chiron\Config\Helper\Validator;

class SettingsConfig extends AbstractInjectableConfig
{
    protected const CONFIG_SECTION_NAME = 'settings';

    protected function getConfigSchema(): Schema
    {
        // TODO : ajouter un champ "environment" ??? qui contiendrait les veleurs "developement" / "production" par exemple !!!!
        return Expect::structure([
            'debug'     => Expect::boolean()->default(false),
            'charset'   => Expect::string()->default('UTF-8')->assert([Validator::class, 'isCharset'], 'charset'),
            'locale'    => Expect::string()->default('en_US')->assert([Validator::class, 'isLocale'], 'locale'),
            'timezone'  => Expect::string()->default('UTC')->assert([Validator::class, 'isTimezone'], 'timezone'),
        ]);
    }

    public function getDebug(): boolean
    {
        return $this->get('debug');
    }

    public function getCharset(): string
    {
        return $this->get('charset');
    }

    public function getLocale(): string
    {
        return $this->get('locale');
    }

    public function getTimezone(): string
    {
        return $this->get('timezone');
    }
}
