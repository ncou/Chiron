<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Middleware;

use Chiron\Http\Factory\ServerRequestFactory;
use Chiron\Http\Middleware\RequestUriTooLongMiddleware;
use Chiron\Http\Psr\Response;
use Chiron\Tests\Utils\HandlerProxy2;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class RequestUriTooLongMiddlewareTest extends TestCase
{
    // define the max uri length (used in the middleware to check if the RequestUriTooLongHttpException should be throwed)
    private $maxUriLength = 2000;

    public function testRequestUriNotTooLong()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => str_pad('', $this->maxUriLength, '*'),
            'REQUEST_METHOD'         => 'GET',
        ]);
        $handler = function ($request) {
            return new Response();
        };

        $middleware = new RequestUriTooLongMiddleware();
        $response = $middleware->process($request, new HandlerProxy2($handler));

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    /**
     * @expectedException Chiron\Http\Exception\RequestUriTooLongHttpException
     */
    public function testRequestUriTooLong()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => str_pad('', $this->maxUriLength + 1, '*'),
            'REQUEST_METHOD'         => 'GET',
        ]);
        $handler = function ($request) {
            return new Response();
        };

        $middleware = new RequestUriTooLongMiddleware();
        $middleware->process($request, new HandlerProxy2($handler));
    }
}
