<?php

declare(strict_types=1);

namespace Chiron\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ResponseTimeMiddleware implements MiddlewareInterface
{
    public const HEADER_NAME = 'X-Response-Time';

    /**
     * Process a server request and return a response.
     * Calculate the execution time based on the request param REQUEST_TIME_FLOAT (present since PHP 5.4.0)
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        return $response->withHeader(self::HEADER_NAME, sprintf('%2.3fms', (microtime(true) - $request->getServerParam('REQUEST_TIME_FLOAT')) * 1000));
    }
}
