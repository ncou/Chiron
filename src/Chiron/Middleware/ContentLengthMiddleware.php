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

        $size = $response->getBody()->getSize();
        if ($size !== null && ! $response->hasHeader('Content-Length') && ! $response->hasHeader('Transfer-Encoding')) {
            $response = $response->withHeader('Content-Length', (string) $size);
        }

        return $response;
    }
}
