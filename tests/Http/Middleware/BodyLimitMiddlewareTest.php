<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Middleware;

use Chiron\Http\Factory\ServerRequestFactory;
use Chiron\Http\Middleware\BodyLimitMiddleware;
use Chiron\Http\Psr\Response;
use Chiron\Tests\Utils\HandlerProxy2;
use PHPUnit\Framework\TestCase;

class BodyLimitMiddlewareTest extends TestCase
{
    public function testBodyNotTooLarge()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'POST',
        ]);

        $request = $request->withHeader('Content-Length', '1024');

        $handler = function ($request) {
            return new Response(200);
        };
        $middleware = new BodyLimitMiddleware();
        $response = $middleware->process($request, new HandlerProxy2($handler));

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @expectedException Chiron\Http\Exception\RequestEntityTooLargeHttpException
     */
    public function testBodyIsTooLarge()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'POST',
        ]);

        $request = $request->withHeader('Content-Length', '10485760');

        $handler = function ($request) {
            return new Response(200);
        };
        $middleware = new BodyLimitMiddleware();
        $middleware->process($request, new HandlerProxy2($handler));
    }

    /**
     * @expectedException Chiron\Http\Exception\BadRequestHttpException
     */
    public function testWithInvalidContentLengthValue()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'POST',
        ]);

        $request = $request->withHeader('Content-Length', '100, 200');

        $handler = function ($request) {
            return new Response(200);
        };
        $middleware = new BodyLimitMiddleware();
        $middleware->process($request, new HandlerProxy2($handler));
    }
}
