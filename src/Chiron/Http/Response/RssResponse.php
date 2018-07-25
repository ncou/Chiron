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
 * RSS response.
 *
 * Allows creating a response by passing an XML string to the constructor; by default,
 * sets a status code of 200 and sets the Content-Type header to application/xml.
 */
class RssResponse extends XmlResponse
{
    protected $contentType = 'application/rss+xml';
}
