<?php

declare(strict_types=1);

namespace Chiron\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/*
 * Sets an "X-Response-Time" response header, indicating the response time of the request, in milliseconds.
 * We use the request param field "REQUEST_TIME_FLOAT" present since PHP 5.4.0 (it's in microseconds).
 */
// TODO : renommer le header en "X-Runtime" et renvoyer des secondes (donc miltiplier par *1000 000 ou 1 puissance 6) et retourner le résultat sur 6 digits dans ajouter l'unité "s" dans la chaine de résultat.
// TODO : utiliser un tableau d'options. + la fonction number_format() pour couper au nombre de digits attendus. => https://github.com/expressjs/response-time/blob/master/index.js#L30
class ResponseTimeMiddleware implements MiddlewareInterface
{
    private const FORMAT_STRING = '%.3f'; // 3 digits
    private const HEADER_NAME = 'X-Response-Time';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $responseTime = sprintf(self::FORMAT_STRING . 'ms', (microtime(true) - $request->getServerParam('REQUEST_TIME_FLOAT')) * 1000);

        return $response->withHeader(self::HEADER_NAME, $responseTime);
    }
}
