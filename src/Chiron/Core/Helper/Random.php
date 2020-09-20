<?php

namespace Chiron\Core\Helper;

final class Random
{
    /**
     * Generate a secure random unique identifier.
     * Length is 32 chars in the range [0123456789abcdef].
     *
     * @return string Random bytes in hexadecimal.
     */
    public static function generateId(): string
    {
        return bin2hex(random_bytes((16)));
    }

    /**
     * Generate a random string.
     *
     * @param int $length      Length of the random string to generate
     * @param bool $easyToRead Prevent ambiguous characters in the result
     *
     * @return string
     */
    public static function generateString(int $length = 26, bool $easyToRead = true): string
    {
        $alphabet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        if ($easyToRead) {
            // remove ambiguous characters.
            $alphabet = str_replace(str_split('B8G6I1l0OQDS5Z2'), '', $alphabet);
        }

        $str = '';
        $alphamax = strlen($alphabet) - 1;
        for ($i = 0; $i < $length; ++$i) {
            $str .= $alphabet[random_int(0, $alphamax)];
        }

        return $str;
    }
}
