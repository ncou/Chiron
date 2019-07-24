<?php

declare(strict_types=1);

namespace Chiron\Boot;

use InvalidArgumentException;
/**
 * Manages application directories.
 */
interface DirectoriesInterface
{
    /**
     * @param string $name Directory alias, ie. "framework".
     * @param string $path Directory path without ending slash.
     */
    public function set(string $name, string $path);

    /**
     * Get directory value.
     *
     * @param string $name
     * @return string
     *
     * @throws InvalidArgumentException When no directory found.
     */
    public function get(string $name): string;

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * List all registered directories.
     *
     * @return array
     */
    public function getAll(): array;
}
