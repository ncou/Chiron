<?php

declare(strict_types=1);

use Chiron\Core\Helper\Random;

if (! function_exists('random_id')) {
    /**
     * Generate a secure random unique identifier.
     * Length is 32 chars in the range [0123456789abcdef].
     *
     * @return string Random bytes in hexadecimal.
     */
    function random_id(): string
    {
        return Random::generateId();
    }
}

if (! function_exists('random_str')) {
    /**
     * Generate a random string.
     *
     * @param int $length      Length of the random string to generate
     * @param bool $easyToRead Prevent ambiguous characters in the result
     *
     * @return string
     */
    function random_str(int $length = 26, bool $easyToRead = true): string
    {
        return Random::generateString($length, $easyToRead);
    }
}
