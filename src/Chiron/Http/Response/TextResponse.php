<?php

declare(strict_types=1);

namespace Chiron\Http\Response;

use Chiron\Http\Psr\Response;
use Chiron\Http\Psr\Stream;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use function get_class;
use function gettype;
use function is_object;
use function is_string;
use function sprintf;

/**
 * Plain text response.
 *
 * Allows creating a response by passing a string to the constructor;
 * by default, sets a status code of 200 and sets the Content-Type header to
 * text/plain.
 */
class TextResponse extends Response
{
    /**
     * Create a plain text response.
     *
     * Produces a text response with a Content-Type of text/plain and a default
     * status of 200.
     *
     * @param string|StreamInterface $text    String or stream for the message body.
     * @param int                    $status  Integer status code for the response; 200 by default.
     * @param array                  $headers Array of headers to use at initialization.
     *
     * @throws InvalidArgumentException if $text is neither a string or stream.
     */
    public function __construct($text, int $status = 200, array $headers = [])
    {
        parent::__construct(
            $status,
            $this->injectContentType('text/plain', $headers),
            $this->createBody($text)
        );
    }

    /**
     * Create the message body.
     *
     * @param string|StreamInterface $text
     *
     * @throws InvalidArgumentException if $html is neither a string or stream.
     *
     * @return StreamInterface
     */
    private function createBody($text): StreamInterface
    {
        if ($text instanceof StreamInterface) {
            return $text;
        }
        if (! is_string($text)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid content (%s) provided to %s',
                (is_object($text) ? get_class($text) : gettype($text)),
                __CLASS__
            ));
        }
        $body = new Stream(fopen('php://temp', 'wb+'));
        $body->write($text);
        $body->rewind();

        return $body;
    }

    /**
     * Inject the provided Content-Type, if none is already present.
     *
     * @param string $contentType
     * @param array  $headers
     *
     * @return array Headers with injected Content-Type
     */
    private function injectContentType(string $contentType, array $headers): array
    {
        if (! array_key_exists('content-type', array_change_key_case($headers))) {
            $headers['content-type'] = [$contentType];
        }

        return $headers;
    }
}
