<?php

declare(strict_types=1);

namespace Chiron\Boot;

/**
 * Provides light abstraction at top of current environment values.
 */
interface EnvironmentInterface
{
    /**
     * Unique environment ID.
     *
     * @return string
     */
    public function hash(): string;
    /**
     * Set environment value.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function set(string $name, $value);
    /**
     * Get environment value.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $name, $default = null);
    /**
     * Return all the environement data.
     *
     * @return array
     */
    public function all(): array;
}
