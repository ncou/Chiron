<?php

declare(strict_types=1);

namespace Chiron\Config;

use Chiron\Config\Helper\Validator;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

final class SettingsConfig extends AbstractInjectableConfig
{
    protected const CONFIG_SECTION_NAME = 'settings';

    protected function getConfigSchema(): Schema
    {
        // TODO : ajouter un champ "name" qui contiendrait le nom de l'application ??? et éventuellement un champ "version" ????
        // TODO : ajouter un champ "environment" ??? qui contiendrait les veleurs "developement" / "production" par exemple !!!!
        return Expect::structure([
            'debug'     => Expect::boolean()->default(env('APP_DEBUG', false)),
            'charset'   => Expect::string()->assert([Validator::class, 'isCharset'], 'charset')->default(env('APP_ENCODING', 'UTF-8')),
            'locale'    => Expect::string()->assert([Validator::class, 'isLocale'], 'locale')->default(env('APP_DEFAULT_LOCALE', 'en_US')),
            'timezone'  => Expect::string()->assert([Validator::class, 'isTimezone'], 'timezone')->default(env('APP_DEFAULT_TIMEZONE', 'UTC')),
        ]);
    }

    public function getDebug(): bool
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
