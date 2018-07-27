<?php

declare(strict_types=1);

namespace Chiron\Http\Response;

use Chiron\Http\Psr\Response;

/**
 * ATOM response.
 *
 * Allows creating a response by passing an XML string to the constructor; by default,
 * sets a status code of 200 and sets the Content-Type header to application/xml.
 */
class AtomResponse extends XmlResponse
{
    protected $contentType = 'application/atom+xml';
}
