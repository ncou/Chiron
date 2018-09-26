<?php

namespace Chiron\Tests\Http\Middleware;

use Chiron\Http\Factory\ServerRequestFactory;
use Chiron\Http\Middleware\ResponseTimeMiddleware;
use Chiron\Http\Psr\Response;
use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;
use Chiron\Tests\Utils\HandlerProxy2;
use PHPUnit\Framework\TestCase;

class ResponseTimeMiddlewareTest extends TestCase
{
    public function testRequestTimeFloat()
    {
        $request = new ServerRequest('GET', new Uri('/'), [], null, '1.1', ['REQUEST_TIME_FLOAT'     => microtime(true)]);

        $handler = function ($request) {
            return new Response();
        };
        $middleware = new ResponseTimeMiddleware();
        $response = $middleware->process($request, new HandlerProxy2($handler));

        $this->assertRegexp('/^\d{1,4}\.\d{3}ms$/', $response->getHeaderLine('X-Response-Time'));
    }
}
