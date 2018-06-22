<?php

declare(strict_types=1);

namespace Chiron\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

// TODO : renommer en RequestTimeMiddleware et écrire dans la request la valeur REQUEST_TIME et REQUEST_TIME_FLOAT si ils n'existent pas !!!!
class ResponseTimeMiddleware implements MiddlewareInterface
{
    public const HEADER = 'X-Response-Time';

    /**
     * Process a server request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $server = $request->getServerParams();

        $startTime = $server['REQUEST_TIME_FLOAT'] ?? microtime(true);

        // TODO : ajouter aussi le 'REQUEST_TIME' : https://gist.github.com/Moln/3e2b08f58cd0ba706436cd55bdf09598#file-swoole-to-psr7-L13

        $response = $handler->handle($request);

        return $response->withHeader(self::HEADER, sprintf('%2.3fms', (microtime(true) - $startTime) * 1000));
    }
}
