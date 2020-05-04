<?php

declare(strict_types=1);

namespace Chiron\Boot;

// TODO : il faudrait surement une méthode pour renvoyer toutes les clés/valeurs du stype "toArray()" ou "all()"
// TODO : https://github.com/oscarotero/env/blob/master/src/Env.php
// TODO : il faut aussi gérer les cas ou il y a des double quottes au début/fin de la chaine.
// TODO : inspiration :   https://github.com/arrilot/dotenv-php/blob/master/src/DotEnv.php
//https://github.com/sebastiansulinski/dotenv/blob/master/src/DotEnv/DotEnv.php#L109

//https://github.com/silverstripe/silverstripe-framework/blob/4/src/Core/Environment.php
//https://github.com/silverstripe/silverstripe-framework/blob/4/src/Core/EnvironmentLoader.php
//https://github.com/vlucas/phpdotenv/blob/master/src/Loader.php
//https://github.com/laravel/lumen-framework/blob/5.8/src/Bootstrap/LoadEnvironmentVariables.php#L52

//https://github.com/vlucas/phpdotenv/blob/master/src/Environment/Adapter/ApacheAdapter.php
//https://github.com/vlucas/phpdotenv/blob/master/src/Environment/Adapter/EnvConstAdapter.php
//https://github.com/vlucas/phpdotenv/blob/master/src/Environment/Adapter/PutenvAdapter.php
//https://github.com/vlucas/phpdotenv/blob/master/src/Environment/Adapter/ServerConstAdapter.php

//https://github.com/pn-neutrino/dotenv/blob/master/src/Dotenv.php
//https://github.com/oscarotero/env/blob/master/src/Env.php

//https://github.com/chillerlan/php-dotenv/blob/master/src/DotEnv.php#L144

// TODO : permettre de faire un getIterator sur cette classe, idem pour utiliser un ArrayAccess pour utiliser cette classe comme un tableau !!!!
// TODO : ajouter dans cette classe une méthode pour vérifier si on est en mode console (cad is_cli) + ajouter cela dans le fichier functions.php
class Environment implements EnvironmentInterface
{
    // TODO : il faudrait pas ajouter à cette liste "yes" et "no" ????
    private const VALUE_MAP = [
        'true'    => true,
        '(true)'  => true,
        'false'   => false,
        '(false)' => false,
        'null'    => null,
        '(null)'  => null,
        'empty'   => '',
    ];

    /** @var string|null */
    private $id = null;

    /** @var array */
    private $values = [];

    /**
     * @param array $values
     */
    // TODO : on devrait injecter le tableau de $values directement dans $_ENV en passant pas la méthode putEnv.
    // TODO : conditionner l'utilisation de putenv comme c'est fait ici : https://github.com/symfony/dotenv/blob/master/Dotenv.php#L139
    public function __construct(array $values = [])
    {
        $this->values = $values + $_ENV + $_SERVER;
        //$this->values = array_merge($_SERVER, $_ENV, $values);
    }

    /**
     * {@inheritdoc}
     */
    // TODO : voir si cette méthode est vraiment utile !!!
    public function hash(): string
    {
        if (empty($this->id)) {
            $this->id = md5(serialize($this->values));
        }

        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $name, $value)
    {
        $this->values[$name] = $_ENV[$name] = $value;
        putenv("$name=$value");
        $this->id = null;
    }

    /**
     * {@inheritdoc}
     */
    // TODO : réfléchir si on garde le paramétre par défaut ou si on l'enléve et qu'on throw une exception si la valeur n'existe pas.
    public function get(string $name, $default = null)
    {
        if (isset($this->values[$name])) {
            return $this->normalize($this->values[$name]);
        }

        return $default;
    }

    // TODO : voir si on garde cette méthode ou si avec la valeur du paramétre par défaut lors du get() cela est suffisant.
    public function has(string $name): bool
    {
        // TODO ; utiliser un array_key_exist() comme dans le classe Directories ????
        return isset($this->values[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return $this->values;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    // TODO : appeller la méthode normalize directement dans le constructeur au lieu de l'appeller dans le get. non ?????
    protected function normalize($value)
    {
        if (! is_string($value)) {
            return $value;
        }
        $alias = strtolower($value);
        if (isset(self::VALUE_MAP[$alias])) {
            return self::VALUE_MAP[$alias];
        }

        return $value;
    }

    /**
     * This handles the the global environment variables, it acts as getenv()
     * that handles the .env file in the root folder of a project.
     *
     * @param string            $key     The constant variable name
     * @param string|bool|mixed $default The default value if it is empty
     *
     * @return mixed The value based on requested variable
     */
    /*
    function env(string $key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
                return true;
            case 'false':
                return false;
            case 'empty':
                return '';
            case 'null':
                return;
        }

        //if (substr($value, 0, 1) === '"' && substr($value, -1) === '"') {
        if (($valueLength = strlen($value)) > 1 && $value[0] === '"' && $value[$valueLength - 1] === '"') {
            return substr($value, 1, -1);
        }

        return $value;
    }*/

    /**
     * Set environment.
     *
     * @param string $key
     * @param mixed  $val
     */
    /*
    public static function setEnv($key, $val)
    {
        if (self::getLoader()->overloader == false) {
            if(self::getEnv($key)) {
                return;
            }
        }
        putenv("{$key}={$val}");
        $_ENV[$key] = $val;
        $_SERVER[$key] = $val;
    }*/

    /**
     * Get environment.
     *
     * @param string $key
     *
     * @return mixed
     */
    /*
        public static function getEnv($key)
        {
            switch (true) {
                case array_key_exists($key, $_ENV):
                    return $_ENV[$key];
                case array_key_exists($key, $_SERVER);
                    return $_SERVER[$key];
                default:
                    return getenv($key);
            }
        }*/

    /**
     * Retrieve the value of the specified environment variable, translating
     * values of 'true', 'false', and 'null' (case-insensitive) to their actual
     * non-string values.
     *
     * PHP's built-in "getenv()" function returns a string (or false, if the
     * environment variable is not set). If the value is 'false', then it will
     * be returned as the string "false", which evaluates to true. This function
     * is to check for that kind of string value and return the actual value
     * that it refers to.
     *
     * NOTE:
     * - If no value is available for the specified environment variable and
     *   no default value was provided, this function returns null (rather than
     *   returning false the way getenv() does).
     * - At version 2.0.0, this method was changed to return the given default
     *   value even if the environment variable exists but has no value (or a
     *   value that only contains whitespace).
     *
     * @param string $varname The name of the desired environment variable.
     * @param mixed  $default The default value to return if the environment
     *                        variable is not set or its value only contains whitespace.
     *
     * @return mixed The resulting value (if set to more than whitespace), or
     *               the given default value (if any, otherwise null).
     */
    /*
        public static function get($varname, $default = null)
        {
            $originalValue = \getenv($varname);

            if ($originalValue === false) {
                return $default;
            }

            $trimmedValue = \trim($originalValue);

            if ($trimmedValue === '') {
                return $default;
            }

            $lowercasedTrimmedValue = \strtolower($trimmedValue);

            if ($lowercasedTrimmedValue === 'false') {
                return false;
            } elseif ($lowercasedTrimmedValue === 'true') {
                return true;
            } elseif ($lowercasedTrimmedValue === 'null') {
                return null;
            }

            return $trimmedValue;
        }*/

    /**
     * Is running through command line.
     *
     * @return bool
     */
    public static function isCLI(): bool
    {
        if ((defined('PHP_SAPI') && PHP_SAPI == 'cli') || (isset($_SERVER['argc']) && $_SERVER['argc'] >= 1)) {
            return true;
        }

        return false;
    }

    /**
     * Return true if PHP running in CLI mode.
     *
     * @codeCoverageIgnore
     *
     * @return bool
     */
    public static function isCLI2(): bool
    {
        if (php_sapi_name() === 'cli') {
            return true;
        }

        return false;
    }

    /**
     * Check, if possible, that this execution was triggered by a command line.
     *
     * @return bool
     */
    public static function isCommandLine(): bool
    {
        return PHP_SAPI == 'cli';
    }

    /*
        public static function cliMode(): bool
        {
            return PHP_SAPI == 'cli';
        }
    */
}
