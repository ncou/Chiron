<?php

declare(strict_types=1);

namespace Chiron\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestUuidMiddleware implements MiddlewareInterface
{
    /**
     * Add a unique ID for each HTTP request.
     *
     * @param ServerRequestInterface  $request request
     * @param RequestHandlerInterface $handler
     *
     * @return object ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uuid = $request->getHeader('X-Request-Id');
        if (empty($uuid)) {
            $uuid = self::uuid();
            $request = $request->withHeader('X-Request-Id', $uuid);
        }

        $response = $handler->handle($request);
        $response = $response->withHeader('X-Request-Id', $uuid);

        return $response;
    }

    /**
     * Generates a v4 random UUID (Universally Unique IDentifier).
     *
     * The version 4 UUID is purely random (except the version).
     * It doesn't contain meaningful information such as MAC address, time, etc.
     *
     * See RFC 4122 for details of UUID.
     *
     * @return string
     */
    public static function uuid(): string
    {
        $bytes = unpack('v*', random_bytes(16));
        $bytes[4] = $bytes[4] & 0x0fff | 0x4000;
        $bytes[5] = $bytes[5] & 0x3fff | 0x8000;

        return vsprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', $bytes);
    }
}
