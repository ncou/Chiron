<?php

namespace Chiron\Support;

//https://github.com/codeigniter4/CodeIgniter4/blob/fed757bee042cf987fea6851753941498e7b73e1/system/Security/Security.php

final class Security
{
    /**
     * Generate a random security key.
     *
     * @return string Random bytes in hexadecimal.
     */
    public static function generateKey(): string
    {
        $key = bin2hex(static::randomBytes());

        return strtoupper($key);
    }

    /**
     * Get random bytes from a secure source.
     *
     * @param int $length The number of bytes you want.
     *
     * @return string Random bytes in binary.
     */
    public static function randomBytes(int $length = 16): string
    {
        return random_bytes($length);
    }

    /**
     * Generate a random string.
     *
     * This is a fork of Joomla JUserHelper::genRandomPassword()
     *
     * @param int $length Length of the random string to generate
     *
     * @return string
     */
    public static function randomString(int $length = 16): string
    {
        $salt = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $base = strlen($salt);
        $password = '';

        /*
         * Start with a cryptographic strength random string, then convert it to
         * a string with the numeric base of the salt.
         * Shift the base conversion on each character so the character
         * distribution is even, and randomize the start shift so it's not
         * predictable.
         */
        $random = static::randomBytes($length + 1);
        $shift = ord($random[0]);

        for ($i = 1; $i <= $length; $i++) {
            $password .= $salt[($shift + ord($random[$i])) % $base];
            $shift += ord($random[$i]);
        }

        return $password;
    }

    /**
     * Sanitize Filename.
     *
     * Tries to sanitize filenames in order to prevent directory traversal attempts
     * and other security threats, which is particularly useful for files that
     * were supplied via user input.
     *
     * If it is acceptable for the user input to include relative paths,
     * e.g. file/in/some/approved/folder.txt, you can set the second optional
     * parameter, $relative_path to TRUE.
     *
     * @param string $str           Input file name
     * @param bool   $relative_path Whether to preserve paths
     *
     * @return string
     */
    //https://github.com/codeigniter4/CodeIgniter4/blob/fed757bee042cf987fea6851753941498e7b73e1/system/Security/Security.php#L366
    /*
    public function sanitizeFilename(string $str, bool $relative_path = false): string
    {
        $bad = $this->filenameBadChars;

        if (! $relative_path)
        {
            $bad[] = './';
            $bad[] = '/';
        }

        $str = remove_invisible_characters($str, false);

        do
        {
            $old = $str;
            $str = str_replace($bad, '', $str);
        }
        while ($old !== $str);

        return stripslashes($str);
    }*/
}
