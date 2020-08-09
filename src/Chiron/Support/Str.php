<?php

namespace Chiron\Support;

// TODO : ajouter des exemples pour chaque fonction. => https://laravel.com/docs/5.4/helpers
// TODO : ajouter des tests !!!
// TODO : ajouter des fonctions globales pour utiliser plus facilement ces méthodes =>   https://github.com/laravel/helpers/blob/25d8562f42ce5bb922f6714e5747094378e9592b/src/helpers.php

//https://github.com/illuminate/support/blob/master/Str.php
//https://github.com/laravel/framework/blob/0b12ef19623c40e22eff91a4b48cb13b3b415b25/tests/Support/SupportStrTest.php

// TODO : renommer la classe en "Case" ???? + ajouter les crédits à Laravel dans la documentation
final class Str
{
    /**
     * Convert a value to camel case.
     *
     * @param  string  $value
     *
     * @return string
     */
    public static function camel(string $value): string
    {
        return lcfirst(static::studly($value));
    }

    /**
     * Convert a string to kebab case.
     *
     * @param  string  $value
     *
     * @return string
     */
    public static function kebab(string $value): string
    {
        return static::snake($value, '-');
    }

    /**
     * Convert the given string to lower-case.
     *
     * @param  string  $value
     *
     * @return string
     */
    public static function lower(string $value): string
    {
        return mb_strtolower($value, 'UTF-8');
    }

    /**
     * Convert the given string to upper-case.
     *
     * @param  string  $value
     *
     * @return string
     */
    public static function upper(string $value): string
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    /**
     * Convert the given string to title case.
     *
     * @param  string  $value
     *
     * @return string
     */
    public static function title(string $value): string
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Convert a string to snake case.
     *
     * @param  string  $value
     * @param  string  $delimiter
     *
     * @return string
     */
    public static function snake(string $value, string $delimiter = '_'): string
    {
        if (! ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));
            $value = static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value));
        }

        return $value;
    }

    /**
     * Convert a value to studly caps case.
     *
     * @param  string  $value
     *
     * @return string
     */
    public static function studly(string $value): string
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return str_replace(' ', '', $value);
    }








    /**
     * Determine if a given string contains a given substring.
     *
     * @param  string  $haystack
     * @param  string|string[]  $needles
     * @return bool
     */
    public static function contains($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}
