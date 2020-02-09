<?php

/**
 * A RFC7231 Compliant date.
 *
 * http://tools.ietf.org/html/rfc7231#section-7.1.1.1
 *
 * Example: Sun, 06 Nov 1994 08:49:37 GMT
 *
 * This constant was introduced in PHP 7.0.19 and PHP 7.1.5 but needs to be defined for earlier PHP versions.
 */
if (! defined('DATE_RFC7231')) {
    // TODO : Remove once the minimal version of PHP is set to 7.1.5
    define('DATE_RFC7231', 'D, d M Y H:i:s \G\M\T');
}

if (! defined('DS')) {
    /*
     * Define DS as short form of DIRECTORY_SEPARATOR.
     */
    define('DS', DIRECTORY_SEPARATOR);
}

if (! function_exists('env')) {
    /**
     * This handles the the global environment variables, it acts as getenv()
     * that handles the .env file in the root folder of a project.
     *
     * @param string            $key     The constant variable name
     * @param string|bool|mixed $default The default value if it is empty
     *
     * @return mixed The value based on requested variable
     */
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
    }
}

if (!function_exists('env_file')) { // @codeCoverageIgnore
    /**
     * Get environment file.
     *
     * @param string $envFile
     * @return string
     */
    function env_file($envFile = '.env')
    {
        if (getenv('APP_ENV')) {
            return $envFile . '.' . getenv('APP_ENV');
        }
        return $envFile;
    }
}

if (! function_exists('is_cli')) {
    /**
     * Check if the application is run in console mode.
     *
     * @return bool
     */
    function is_cli()
    {
        return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
    }
}

// TODO : fonction à renommer en html_encode() ou escape_html()
if (!function_exists('h')) {
    /**
     * Convenience method for htmlspecialchars.
     *
     * @param mixed $text Text to wrap through htmlspecialchars. Also works with arrays, and objects.
     *    Arrays will be mapped and have all their elements escaped. Objects will be string cast if they
     *    implement a `__toString` method. Otherwise the class name will be used.
     *    Other scalar types will be returned unchanged.
     * @param bool $double Encode existing html entities.
     * @param string|null $charset Character set to use when escaping.
     *   Defaults to config value in `mb_internal_encoding()` or 'UTF-8'.
     * @return mixed Wrapped text.
     * @link https://book.cakephp.org/4/en/core-libraries/global-constants-and-functions.html#h
     */
    function h($text, bool $double = true, ?string $charset = null)
    {
        if (is_string($text)) {
            //optimize for strings
        } elseif (is_array($text)) {
            $texts = [];
            foreach ($text as $k => $t) {
                $texts[$k] = h($t, $double, $charset);
            }
            return $texts;
        } elseif (is_object($text)) {
            if (method_exists($text, '__toString')) {
                $text = $text->__toString();
            } else {
                $text = '(object)' . get_class($text);
            }
        } elseif ($text === null || is_scalar($text)) {
            return $text;
        }
        static $defaultCharset = false;
        if ($defaultCharset === false) {
            $defaultCharset = mb_internal_encoding() ?: 'UTF-8';
        }
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, $charset ?: $defaultCharset, $double);
    }
}

if (! function_exists('pr')) {
    /**
     * print_r() convenience function.
     *
     * In terminals this will act similar to using print_r() directly, when not run on cli
     * print_r() will also wrap <pre> tags around the output of given variable. Similar to debug().
     *
     * This function returns the same variable that was passed.
     *
     * @param mixed $var Variable to print out.
     *
     * @return mixed the same $var that was passed to this function
     *
     * @see https://book.cakephp.org/3.0/en/core-libraries/global-constants-and-functions.html#pr
     * @see debug()
     */
    function pr($var)
    {
        $template = is_cli() ? "\n%s\n\n" : '<pre class="pr">%s</pre>';
        printf($template, trim(print_r($var, true)));

        return $var;
    }
}
if (! function_exists('pj')) {
    /**
     * json pretty print convenience function.
     *
     * In terminals this will act similar to using json_encode() with JSON_PRETTY_PRINT directly, when not run on cli
     * will also wrap <pre> tags around the output of given variable. Similar to pr().
     *
     * This function returns the same variable that was passed.
     *
     * @param mixed $var Variable to print out.
     *
     * @return mixed the same $var that was passed to this function
     *
     * @see pr()
     * @see https://book.cakephp.org/3.0/en/core-libraries/global-constants-and-functions.html#pj
     */
    function pj($var)
    {
        $template = is_cli() ? "\n%s\n\n" : '<pre class="pj">%s</pre>';
        printf($template, trim(json_encode($var, JSON_PRETTY_PRINT)));

        return $var;
    }
}

// TODO : déplacer cette fonction dans une classe du répertoire Helper du genre Str.php ou Text.php
// https://github.com/Seldaek/monolog/blob/master/src/Monolog/Utils.php#L19
if (! function_exists('namespaceSplit')) {
    /**
     * Split the namespace from the classname.
     *
     * Commonly used like `list($namespace, $className) = namespaceSplit($class);`.
     *
     * @param string $class The full class name, ie `Cake\Core\App`.
     *
     * @return array Array with 2 indexes. 0 => namespace, 1 => classname.
     */
    function namespaceSplit(string $class): array
    {
        $pos = strrpos($class, '\\');
        if ($pos === false) {
            return ['', $class];
        }

        return [substr($class, 0, $pos), substr($class, $pos + 1)];
    }
}
