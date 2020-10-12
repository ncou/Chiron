<?php

namespace Chiron\Core\Helper;

//https://docs.phalcon.io/4.0/fr-fr/api/phalcon_security#security-random

/*
$random = new Random();

// ...
$bytes      = $random->bytes();

// Generate a random hex string of length $len.
$hex        = $random->hex($len);

// Generate a random base64 string of length $len.
$base64     = $random->base64($len);

// Generate a random URL-safe base64 string of length $len.
$base64Safe = $random->base64Safe($len);

// Generate a UUID (version 4).
// See https://en.wikipedia.org/wiki/Universally_unique_identifier
$uuid       = $random->uuid();

// Generate a random integer between 0 and $n.
$number     = $random->number($n);
*/

final class Random
{
    /**
     * Generate a secure random unique hexadecimal identifier.
     * Output characters is $length X 2 in the range [0123456789abcdef].
     *
     * @param int $length
     *
     * @return string
     */
    public static function generateId(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Generate a random string.
     *
     * @param int $length      Length of the random string to generate
     * @param bool $easyToRead Prevent ambiguous characters in the result
     *
     * @return string
     */
    public static function generateString(int $length = 32, bool $easyToRead = false): string
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

/*
    function str_rand(int $length = 64){ // 64 = 32
        $length = ($length < 4) ? 4 : $length;
        return bin2hex(random_bytes(($length-($length%2))/2));
    }

    var_dump(str_rand());
    // d6199909d0b5fdc22c9db625e4edf0d6da2b113b21878cde19e96f4afe69e714
    */
}
