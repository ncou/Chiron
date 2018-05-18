<?php

declare(strict_types=1);

namespace Chiron\Tests\Middleware;

use Chiron\Http\Factory\ServerRequestFactory;
use Chiron\Http\Response;
use Chiron\Http\Uri;
use Chiron\Middleware\BodyLimitMiddleware;
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
}
