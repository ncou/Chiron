<?php


declare(strict_types=1);

namespace Chiron\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;


//https://github.com/zendframework/zend-diactoros/blob/master/src/Response/Serializer.php
//https://github.com/zendframework/zend-diactoros/blob/master/src/Request/Serializer.php
//https://github.com/zendframework/zend-diactoros/blob/master/src/AbstractSerializer.php

abstract class Serializer {

    //private const CR  = "\r";
    private const EOL = "\r\n";
    //private const LF  = "\n";

    /**
     * Create a string representation of a response.
     *
     * @param ResponseInterface $response
     * @return string
     */
    public static function responseToString(ResponseInterface $response): string
    {
        $reasonPhrase = $response->getReasonPhrase();
        $headers      = self::serializeHeaders($response->getHeaders());
        $body         = (string) $response->getBody();
        $format       = 'HTTP/%s %d%s%s%s';
        if (! empty($headers)) {
            $headers = "\r\n" . $headers;
        }
        $headers .= "\r\n\r\n";
        return sprintf(
            $format,
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            ($reasonPhrase ? ' ' . $reasonPhrase : ''),
            $headers,
            $body
        );
    }


    /**
     * Serialize a request message to a string.
     *
     * @param RequestInterface $request
     * @return string
     */
    public static function requestToString(RequestInterface $request): string
    {
        $httpMethod = $request->getMethod();
        if (empty($httpMethod)) {
            throw new UnexpectedValueException('Object can not be serialized because HTTP method is empty');
        }
        $headers = self::serializeHeaders($request->getHeaders());
        $body    = (string) $request->getBody();
        $format  = '%s %s HTTP/%s%s%s';
        if (! empty($headers)) {
            $headers = "\r\n" . $headers;
        }
        if (! empty($body)) {
            $headers .= "\r\n\r\n";
        }
        return sprintf(
            $format,
            $httpMethod,
            $request->getRequestTarget(),
            $request->getProtocolVersion(),
            $headers,
            $body
        );
    }

    /**
     * Serialize headers to string values.
     *
     * @param array $headers
     * @return string
     */
    protected static function serializeHeaders(array $headers): string
    {
        $lines = [];
        foreach ($headers as $header => $values) {
            $normalized = self::filterHeader($header);
            foreach ($values as $value) {
                $lines[] = sprintf('%s: %s', $normalized, $value);
            }
        }
        return implode("\r\n", $lines);
    }
    /**
     * Filter a header name to wordcase
     *
     * @param string $header
     * @return string
     */
    protected static function filterHeader(string $header): string
    {
        $filtered = str_replace('-', ' ', $header);
        $filtered = ucwords($filtered);
        return str_replace(' ', '-', $filtered);
    }
}
