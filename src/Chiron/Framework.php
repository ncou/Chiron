<?php

declare(strict_types=1);

namespace Chiron;

//https://github.com/swoft-cloud/swoft-component/blob/master/src/framework/src/Swoft.php

/**
 * Framework properties (name/version/path/logo).
 */
final class Framework
{
    private const NAME = 'ChironPHP';

    private const VERSION = '1.0.0';

    /**
     * Chiron terminal logo
     *
     * @see http://patorjk.com/software/taag/#p=display&f=Slant&t=Chiron%201.0
     */
    private const LOGO = "
   ________    _                     ___ ____
  / ____/ /_  (_)________  ____     <  // __ \
 / /   / __ \/ / ___/ __ \/ __ \    / // / / /
/ /___/ / / / / /  / /_/ / / / /   / // /_/ /
\____/_/ /_/_/_/   \____/_/ /_/   /_(_)____/
";

    /**
     * Chiron terminal logo small
     *
     * @see http://patorjk.com/software/taag/#p=display&f=Small%20Slant&t=Chiron%201.0
     */
    private const LOGO_SMALL = "
  _______   _                 ___ ___
 / ___/ /  (_)______  ___    <  // _ \
/ /__/ _ \/ / __/ _ \/ _ \   / // // /
\___/_//_/_/_/  \___/_//_/  /_(_)___/
";

    /**
     * Chiron server start banner logo
     */
    private const BANNER_LOGO = "
  _______   _                 ____                                   __
 / ___/ /  (_)______  ___    / __/______ ___ _  ___ _    _____  ____/ /__
/ /__/ _ \/ / __/ _ \/ _ \  / _// __/ _ `/  ' \/ -_) |/|/ / _ \/ __/  '_/
\___/_//_/_/_/  \___/_//_/ /_/ /_/  \_,_/_/_/_/\__/|__,__/\___/_/ /_/\_\
";

    public static function name(): string
    {
        return self::NAME;
    }

    public static function version(): string
    {
        return self::VERSION;
    }

    public static function fullname(): string
    {
        return sprintf('%s v%s', self::NAME, self::VERSION);
    }

    public static function path(): string
    {
        // TODO : appeller la méthode normalizeDir() de la classe Path::class
        //return str_replace('\\', '/', dirname(__DIR__, 2)) . '/';
        return dirname(__DIR__, 2);
    }

    // TODO : passer des paramétres booléens pour faire un ltrim et rtrim sur PHP_EOL pour éviter les retours à la lignes qui sont présents dans les LOGO ????
    public static function logo(bool $small = false): string
    {
        return $small ? self::LOGO_SMALL : self::LOGO;
    }

    // TODO : passer des paramétres booléens pour faire un ltrim et rtrim sur PHP_EOL pour éviter les retours à la lignes qui sont présents dans les LOGO ????
    public static function banner(): string
    {
        return self::BANNER_LOGO;
    }

    /**
     * Return true if PHP running in CLI mode.
     *
     * @codeCoverageIgnore
     * @return bool
     */
    /*
    public static function isCLI(): bool
    {
        if (!empty(getenv('RR'))) {
            // Do not treat RoadRunner as CLI.
            return false;
        }

        if (php_sapi_name() === 'cli') {
            return true;
        }

        return false;
    }*/

    /**
     * Gets whether or not the application is running in a console
     *
     * @return bool True if the application is running in a console, otherwise false
     */
    /*
    public static function isRunningInConsole() : bool
    {
        return php_sapi_name() === 'cli';
    }*/
}
