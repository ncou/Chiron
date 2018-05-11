<?php

declare(strict_types=1);

namespace Chiron\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestUuidMiddleware implements MiddlewareInterface
{
    private const HEADER_NAME = 'X-Request-Id';

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
        $uuid = $request->getHeader(self::HEADER_NAME);
        if (empty($uuid)) {
            // generate a 32 char string unique user id
            $uuid = bin2hex(random_bytes(16));
            $request = $request->withHeader(self::HEADER_NAME, $uuid);
        }

        $response = $handler->handle($request);
        $response = $response->withHeader(self::HEADER_NAME, $uuid);

        return $response;
    }

    // TODO : regarder pour conditionner l'ajout du header sur la r�ponse seulement si c'est d�fini par l'utilisateur, et possibilit� d'utiliser un autre header name
    // https://github.com/qandidate-labs/stack-request-id/blob/master/src/Qandidate/Stack/RequestId.php#L58
}
