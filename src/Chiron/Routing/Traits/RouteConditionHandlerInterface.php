<?php

declare(strict_types=1);

namespace Chiron\Routing\Traits;

interface RouteConditionHandlerInterface
{
    /**
     * Get the host condition.
     *
     * @return string|null
     */
    public function getHost(): ?string;

    /**
     * Set the host condition.
     *
     * @param string $host
     *
     * @return static
     */
    public function setHost(string $host): RouteConditionHandlerInterface;

    /**
     * Alia function for "setHost()".
     *
     * @param string $host
     *
     * @return static
     */
    public function host(string $host): RouteConditionHandlerInterface;

    /**
     * Get the scheme condition.
     *
     * @return string|null
     */
    public function getScheme(): ?string;

    /**
     * Set the scheme condition.
     *
     * @param string $scheme
     *
     * @return static
     */
    public function setScheme(string $scheme): RouteConditionHandlerInterface;

    /**
     * Alia function for "setScheme()".
     *
     * @param string $scheme
     *
     * @return static
     */
    public function scheme(string $scheme): RouteConditionHandlerInterface;

    /**
     * Helper - Sets the scheme requirement to HTTP (no HTTPS).
     *
     * @param string $scheme
     *
     * @return static
     */
    public function requireHttp(): RouteConditionHandlerInterface;

    /**
     * Helper - Sets the scheme requirement to HTTPS.
     *
     * @param string $scheme
     *
     * @return static
     */
    public function requireHttps(): RouteConditionHandlerInterface;

    /**
     * Get the port condition.
     *
     * @return int|null
     */
    public function getPort(): ?int;

    /**
     * Set the port condition.
     *
     * @param int $port
     *
     * @return static
     */
    public function setPort(int $port): RouteConditionHandlerInterface;

    /**
     * Alia function for "setPort()".
     *
     * @param int $port
     *
     * @return static
     */
    public function port(int $port): RouteConditionHandlerInterface;
}
