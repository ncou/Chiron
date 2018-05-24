<?php

declare(strict_types=1);

namespace Chiron\Tests\Middleware;

use Chiron\Http\Factory\ServerRequestFactory;
use Chiron\Http\Response;
//use Chiron\Http\Uri;
use Chiron\Middleware\ResponseTimeMiddleware;
use Chiron\Tests\Utils\HandlerProxy2;
use PHPUnit\Framework\TestCase;

class ResponseTimeMiddlewareTest extends TestCase
{
    public function testResponseTime()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
        ]);
        $handler = function ($request) {
            return new Response();
        };
        $middleware = new ResponseTimeMiddleware();
        $response = $middleware->process($request, new HandlerProxy2($handler));

        $this->assertRegexp('/^\d{1,4}\.\d{3}ms$/', $response->getHeaderLine('X-Response-Time'));
    }

    public function testRequestTimeFloat()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
            'REQUEST_TIME_FLOAT'     => microtime(true),
        ]);
        $handler = function ($request) {
            return new Response();
        };
        $middleware = new ResponseTimeMiddleware();
        $response = $middleware->process($request, new HandlerProxy2($handler));

        $this->assertRegexp('/^\d{1,4}\.\d{3}ms$/', $response->getHeaderLine('X-Response-Time'));
    }
}
