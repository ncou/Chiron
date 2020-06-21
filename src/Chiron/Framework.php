<?php

declare(strict_types=1);

namespace Chiron;

//https://github.com/swoft-cloud/swoft-component/blob/master/src/framework/src/Swoft.php

/**
 * Store the Framework name and version.
 */
// TODO : renommer cette classe en "Chiron" ????
// TODO : ajouter le logo qui sera affiché dans les commandes pour la console
final class Framework
{
    public static function name(): string
    {
        return 'ChironPHP';
    }

    public static function version(): string
    {
        return '1.0.0';
    }

    public static function fullname(): string
    {
        return sprintf('%s v%s', static::name(), static::version());
    }

    public static function path(): string
    {
        return dirname(__DIR__, 2);
    }
}
