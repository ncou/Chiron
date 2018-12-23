<?php

declare(strict_types=1);

namespace Chiron\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestIdMiddleware implements MiddlewareInterface
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
            // generate an "unique user id"
            $uuid = $this->uuid();
            $request = $request->withHeader(self::HEADER_NAME, $uuid);
        }

        $response = $handler->handle($request);
        $response = $response->withHeader(self::HEADER_NAME, $uuid);

        return $response;
    }

    // TODO : regarder pour conditionner l'ajout du header sur la réponse seulement si c'est défini par l'utilisateur, et possibilité d'utiliser un autre header name
    // https://github.com/qandidate-labs/stack-request-id/blob/master/src/Qandidate/Stack/RequestId.php#L58

    /**
     * Generate a version 4 (random) UUID.
     *
     * @see https://tools.ietf.org/html/rfc4122#section-4.4
     * @see https://en.wikipedia.org/wiki/Universally_unique_identifier#Version_4_(random)
     *
     * @return string
     */
    private function uuid(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
