<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Middleware;

use Chiron\Http\Factory\ServerRequestFactory;
use Chiron\Http\Middleware\RequestLimitationsMiddleware;
use Chiron\Http\Psr\Response;
use Chiron\Tests\Utils\HandlerProxy2;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class RequestLimitationsMiddlewareTest extends TestCase
{
    // define the max uri length (used in the middleware to check if the RequestUriTooLongHttpException should be throwed)
    private $maxUriLength = 2048;

    private $maxNumHeaders = 100;

    private $maxHeadersValue = 4096;

    private $maxHeaderValue = 2048;

    private $maxHeaderName = 64;

    public function testRequestUriNotTooLong()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => str_pad('', $this->maxUriLength, '*'),
            'REQUEST_METHOD'         => 'GET',
        ]);
        $handler = function ($request) {
            return new Response();
        };

        $middleware = new RequestLimitationsMiddleware();
        $response = $middleware->process($request, new HandlerProxy2($handler));

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    /**
     * @expectedException Chiron\Http\Exception\Client\RequestUriTooLongHttpException
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

        $middleware = new RequestLimitationsMiddleware();
        $middleware->process($request, new HandlerProxy2($handler));
    }

    /**
     * @expectedException Chiron\Http\Exception\Client\RequestHeaderFieldsTooLargeHttpException
     */
    public function testTooMuchHeaders()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
        ]);
        $handler = function ($request) {
            return new Response();
        };

        for ($index = 1; $index <= $this->maxNumHeaders + 1; $index++) {
            $request = $request->withHeader('X-Custom_' . $index, ['TEST']);
        }

        $middleware = new RequestLimitationsMiddleware();
        $middleware->process($request, new HandlerProxy2($handler));
    }

    /**
     * @expectedException Chiron\Http\Exception\Client\RequestHeaderFieldsTooLargeHttpException
     */
    public function testAllHeadersTooBig()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
        ]);
        $handler = function ($request) {
            return new Response();
        };

        for ($index = 1; $index <= 100; $index++) {
            $request = $request->withHeader('X-Custom_' . $index, [str_pad('', intval($this->maxHeadersValue / 100), '*')]);
        }

        $middleware = new RequestLimitationsMiddleware();
        $middleware->process($request, new HandlerProxy2($handler));
    }

    /**
     * @expectedException Chiron\Http\Exception\Client\RequestHeaderFieldsTooLargeHttpException
     */
    public function testOneHeaderTooBig()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
        ]);
        $handler = function ($request) {
            return new Response();
        };

        $request = $request->withHeader('X-Very-Long-Header-Value', [str_pad('', $this->maxHeaderValue, '*')]);

        $middleware = new RequestLimitationsMiddleware();
        $middleware->process($request, new HandlerProxy2($handler));
    }

    /**
     * @expectedException Chiron\Http\Exception\Client\RequestHeaderFieldsTooLargeHttpException
     */
    public function testHeaderNameTooLong()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
        ]);
        $handler = function ($request) {
            return new Response();
        };

        $request = $request->withHeader(str_pad('X-Very-Long-Header-Name', $this->maxHeaderName + 1, '*'), ['TEST']);

        $middleware = new RequestLimitationsMiddleware();
        $middleware->process($request, new HandlerProxy2($handler));
    }
}
