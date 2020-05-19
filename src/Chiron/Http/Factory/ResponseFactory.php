<?php

declare(strict_types=1);

namespace Chiron\Http\Factory;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

//https://github.com/thephpleague/route/blob/master/src/Strategy/AbstractStrategy.php
//https://github.com/zendframework/zend-diactoros/blob/0bae78192e634774b5584f0210c1232da82cb1ff/src/Response/InjectContentTypeTrait.php
//https://github.com/zendframework/zend-http/blob/9812b6e14b8e94ac0bfaece955bc863df2fcf309/src/Header/ContentType.php
//https://github.com/zendframework/zend-mail/blob/ece418b37aaf8a98c991d7f0c198408043a2172d/src/Header/ContentType.php

/**
 * Wrapper for the PSR17 ResponseFactory to inject some default headers.
 */
final class ResponseFactory implements ResponseFactoryInterface
{
    /** @var ResponseFactoryInterface */
    private $factory;

    /** @var array */
    private $headers;

    /**
     * @param ResponseFactoryInterface $factory
     * @param array                    $headers
     */
    public function __construct(ResponseFactoryInterface $factory, array $headers)
    {
        $this->factory = $factory;
        $this->headers = $headers;
    }

    /**
     * Create response dans apply default response headers.
     *
     * Headers that already exist on the response will NOT be replaced.
     *
     * @param int    $code
     * @param string $reasonPhrase
     *
     * @return ResponseInterface
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        $response = $this->factory->createResponse($code, $reasonPhrase);

        foreach ($this->headers as $header => $value) {
            //$response = $response->withAddedHeader($header, $value);

            if ($response->hasHeader($header) === false) {
                $response = $response->withHeader($header, $value);
            }
        }

        return $response;
    }

    /**
     * Inject the provided Content-Type, if none is already present.
     *
     * @return array Headers with injected Content-Type
     */
    /*
    private function injectContentType(string $contentType, array $headers) : array
    {
        $hasContentType = array_reduce(array_keys($headers), function ($carry, $item) {
            return $carry ?: (strtolower($item) === 'content-type');
        }, false);
        if (! $hasContentType) {
            $headers['content-type'] = [$contentType];
        }
        return $headers;
    }*/

    /** @var array */
    //protected $defaultResponseHeaders = [];
    /**
     * Get current default response headers.
     *
     * @return array
     */
    /*
    public function getDefaultResponseHeaders(): array
    {
        return $this->defaultResponseHeaders;
    }*/
    /**
     * Add or replace a default response header.
     *
     * @param string $name
     * @param string $value
     *
     * @return static
     */
    /*
    public function addDefaultResponseHeader(string $name, string $value): AbstractStrategy
    {
        $this->defaultResponseHeaders[strtolower($name)] = $value;
        return $this;
    }*/
    /**
     * Add multiple default response headers.
     *
     * @param array $headers
     *
     * @return static
     */
    /*
    public function addDefaultResponseHeaders(array $headers): AbstractStrategy
    {
        foreach ($headers as $name => $value) {
            $this->addDefaultResponseHeader($name, $value);
        }
        return $this;
    }*/
    /**
     * Apply default response headers.
     *
     * Headers that already exist on the response will NOT be replaced.
     *
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    /*
    protected function applyDefaultResponseHeaders(ResponseInterface $response): ResponseInterface
    {
        foreach ($this->defaultResponseHeaders as $name => $value) {
            if (false === $response->hasHeader($name)) {
                $response = $response->withHeader($name, $value);
            }
        }
        return $response;
    }*/

    /**
     * Formats the Content-Type header based on the configured contentType and charset
     * the charset will only be set in the header if the response is of type text/*.
     *
     * @param string $type The type to set.
     */
    //https://github.com/cakephp/cakephp/blob/master/src/Http/Response.php#L486
    /*
    protected function _setContentType(string $type): void
    {
        if (in_array($this->_status, [304, 204], true)) {
            $this->_clearHeader('Content-Type');

            return;
        }
        $whitelist = [
            'application/javascript', 'application/xml', 'application/rss+xml',
        ];

        $charset = false;
        if (
            $this->_charset &&
            (
                strpos($type, 'text/') === 0 ||
                in_array($type, $whitelist, true)
            )
        ) {
            $charset = true;
        }

        if ($charset && strpos($type, ';') === false) {
            $this->_setHeader('Content-Type', "{$type}; charset={$this->_charset}");
        } else {
            $this->_setHeader('Content-Type', $type);
        }
    }
    */
}
