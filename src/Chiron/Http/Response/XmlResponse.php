<?php

declare(strict_types=1);

namespace Chiron\Http\Response;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Chiron\Http\Psr\Response;
use Chiron\Http\Psr\Stream;
use function get_class;
use function gettype;
use function is_object;
use function is_string;
use function sprintf;
/**
 * XML response.
 *
 * Allows creating a response by passing an XML string to the constructor; by default,
 * sets a status code of 200 and sets the Content-Type header to application/xml.
 */
class XmlResponse extends Response
{
    /**
     * Create an XML response.
     *
     * Produces an XML response with a Content-Type of application/xml and a default
     * status of 200.
     *
     * @param string|StreamInterface $xml String or stream for the message body.
     * @param int $status Integer status code for the response; 200 by default.
     * @param array $headers Array of headers to use at initialization.
     * @throws InvalidArgumentException if $text is neither a string or stream.
     */
    public function __construct(
        $xml,
        int $status = 200,
        array $headers = []
    ) {
        parent::__construct(
            $status,
            $this->injectContentType('application/xml; charset=utf-8', $headers),
            $this->createBody($xml)
        );
    }
    /**
     * Create the message body.
     *
     * @param string|StreamInterface $xml
     * @return StreamInterface
     * @throws InvalidArgumentException if $xml is neither a string or stream.
     */
    private function createBody($xml)
    {
        if ($xml instanceof StreamInterface) {
            return $xml;
        }
        if (! is_string($xml)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid content (%s) provided to %s',
                (is_object($xml) ? get_class($xml) : gettype($xml)),
                __CLASS__
            ));
        }
        $body = new Stream(fopen('php://temp', 'wb+'));
        $body->write($xml);
        $body->rewind();
        return $body;
    }

    /**
     * Inject the provided Content-Type, if none is already present.
     *
     * @param string $contentType
     * @param array $headers
     * @return array Headers with injected Content-Type
     */
    // TODO : à virer !!!!
    private function injectContentType($contentType, array $headers)
    {
        $hasContentType = array_reduce(array_keys($headers), function ($carry, $item) {
            return $carry ?: (strtolower($item) === 'content-type');
        }, false);
        if (! $hasContentType) {
            $headers['content-type'] = [$contentType];
        }
        return $headers;
    }
}
