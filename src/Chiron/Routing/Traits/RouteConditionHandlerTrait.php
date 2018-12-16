<?php

declare(strict_types=1);

namespace Chiron\Routing\Traits;

trait RouteConditionHandlerTrait
{
    /**
     * @var string|null
     */
    protected $host;

    /**
     * @var string|null
     */
    protected $scheme;

    /**
     * @var int|null
     */
    protected $port;

    /**
     * {@inheritdoc}
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * {@inheritdoc}
     */
    public function setHost(string $host): RouteConditionHandlerInterface
    {
        $this->host = $host;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function host(string $host): RouteConditionHandlerInterface
    {
        return $this->setHost($host);
    }

    /**
     * {@inheritdoc}
     */
    public function getScheme(): ?string
    {
        return $this->scheme;
    }

    /**
     * {@inheritdoc}
     */
    public function setScheme(string $scheme): RouteConditionHandlerInterface
    {
        $this->scheme = strtolower($scheme);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function scheme(string $scheme): RouteConditionHandlerInterface
    {
        return $this->setScheme($scheme);
    }


    /**
     * {@inheritdoc}
     */
    public function requireHttp(): RouteConditionHandlerInterface
    {
        return $this->setScheme('http');
    }

    /**
     * {@inheritdoc}
     */
    public function requireHttps(): RouteConditionHandlerInterface
    {
        return $this->setScheme('https');
    }


    /**
     * {@inheritdoc}
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * {@inheritdoc}
     */
    public function setPort(int $port): RouteConditionHandlerInterface
    {
        $this->port = $port;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function port(int $port): RouteConditionHandlerInterface
    {
        return $this->setPort($port);
    }
}
