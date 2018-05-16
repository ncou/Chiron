<?php
/**
 * Chiron Framework.
 *
 * @see      https://github.com/ncou/Chiron
 *
 * @copyright Copyright (c) 2011-2018 Josh Lockhart
 * @license   https://github.com/ncou/Chiron/blob/master/LICENSE.md (MIT License)
 */
declare(strict_types=1);

namespace Chiron\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ContentLengthMiddleware implements MiddlewareInterface
{
    /**
     * Add Content-Length header to the response if not already added previously.
     * According to RFC2616 section 4.4, we MUST ignore Content-Length: header if we are now receiving data using chunked Transfer-Encoding.
     *
     * @see http://www.ietf.org/rfc/rfc2616.txt
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        // Don't add the content-length header if transfert-encoding is present.
        if ($response->hasHeader('Transfer-Encoding')) {
            // And remove content-length if both headers are presents (according to RFC2616).
            if ($response->hasHeader('Content-Length')) {
                $response = $response->withoutHeader('Content-Length');
            }
        } elseif (! $response->hasHeader('Content-Length')) {
            $size = $response->getBody()->getSize();

            if ($size !== null) {
                $response = $response->withHeader('Content-Length', (string) $size);
            }
        }

        return $response;

/*
        // Don't add the content-length header if transfert-encoding is present.
        if ($response->hasHeader('Transfer-Encoding')) {
            if ($response->hasHeader('Content-Length')) {
                // And remove content-length if both headers are presents (according to RFC2616).
                return $response->withoutHeader('Content-Length');
            }
            return $response;
        }

        if ($response->hasHeader('Content-Length')) {
            return $response;
        }

        $size = $response->getBody()->getSize();
        if ($size === null) {
            return $response;
        }

        return $response->withHeader('Content-Length', (string) $size);
*/

    }

}
