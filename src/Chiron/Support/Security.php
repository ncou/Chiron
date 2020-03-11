<?php

namespace Chiron\Support;

class Security
{

    /**
     * Get random bytes from a secure source.
     *
     * This method will fall back to an insecure source an trigger a warning
     * if it cannot find a secure source of random data.
     *
     * @param int $length The number of bytes you want.
     * @return string Random bytes in binary.
     */
    public static function randomBytes(int $length = 16): string
    {
        return random_bytes($length);
    }

    /**
     * Generate a random password.
     *
     * This is a fork of Joomla JUserHelper::genRandomPassword()
     *
     * @param  integer  $length  Length of the password to generate
     *
     * @return  string  Random Password
     *
     * @throws \Exception
     * @since   2.0.9
     * @see     https://github.com/joomla/joomla-cms/blob/staging/libraries/joomla/user/helper.php#L642
     */
    public static function randomString(int $length = 16): string
    {
        $salt     = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $base     = strlen($salt);
        $password = '';

        /*
         * Start with a cryptographic strength random string, then convert it to
         * a string with the numeric base of the salt.
         * Shift the base conversion on each character so the character
         * distribution is even, and randomize the start shift so it's not
         * predictable.
         */
        $random = static::randomBytes($length + 1);
        $shift  = ord($random[0]);

        for ($i = 1; $i <= $length; ++$i) {
            $password .= $salt[($shift + ord($random[$i])) % $base];

            $shift += ord($random[$i]);
        }

        return $password;
    }

    public static function generateKey(): string
    {
        $key = bin2hex(static::randomBytes());

        return strtoupper($key);
    }

}
