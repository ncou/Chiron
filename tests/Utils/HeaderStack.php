<?php
declare(strict_types=1);

namespace Chiron\Tests\Utils;

class HeaderStack
{
    /**
     * @var string[][]
     */
    private static $data = [];
    /**
     * Reset state.
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$data = [];
    }
    /**
     * Push a header on the stack.
     *
     * @param string[] $header
     */
    public static function push(array $header): void
    {
        self::$data[] = $header;
    }
    /**
     * Return the current header stack.
     *
     * @return string[][]
     */
    public static function stack(): array
    {
        return self::$data;
    }
    /**
     * Verify if there's a header line on the stack.
     *
     * @param string $header
     *
     * @return bool
     */
    public static function has($header)
    {
        foreach (self::$data as $item) {
            if ($item['header'] === $header) {
                return true;
            }
        }
        return false;
    }
}
