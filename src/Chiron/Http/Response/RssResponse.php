<?php

declare(strict_types=1);

namespace Chiron\Http\Response;

use Chiron\Http\Psr\Response;

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
