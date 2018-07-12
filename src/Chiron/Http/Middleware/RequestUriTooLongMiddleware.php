<?php

declare(strict_types=1);

namespace Chiron\Http\Middleware;

use Chiron\Http\Exception\RequestUriTooLongHttpException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestUriTooLongMiddleware implements MiddlewareInterface
{
    /**
     * Throw an HTTP 414 Exception if the URI is too long.
     *
     * @param ServerRequestInterface  $request request
     * @param RequestHandlerInterface $handler
     *
     * @return object ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Max allowed uri length is set at 2000 characters
        if (strlen($request->getServerParam('REQUEST_URI')) > 2000) {
            throw new RequestUriTooLongHttpException();
        }

        return $handler->handle($request);
    }
}
