<?php

declare(strict_types=1);

namespace Chiron\Tests\Routing\Resolver\Fixtures;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Http\Psr\Response;

/**
 * Mock object ControllerResolverTest
 */
class RequestHandlerTest implements RequestHandlerInterface
{
    public static $CalledCount = 0;
    public static $strategy = '';

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        static::$CalledCount++;


        $response = (new Response(200))->withHeader('Content-Type', 'text/plain');
        $calledCount = static::$CalledCount;
        $response->getBody()->write("{$calledCount}");

        return $response;
    }
}
