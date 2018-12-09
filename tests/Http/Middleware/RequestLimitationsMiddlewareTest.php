<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Middleware;

use Chiron\Http\Middleware\RequestLimitationsMiddleware;
use Chiron\Http\Psr\Response;
use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;
use Chiron\Tests\Utils\RequestHandlerCallable;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class RequestLimitationsMiddlewareTest extends TestCase
{
    // define the max uri length (used in the middleware to check if the UriTooLongHttpException should be throwed)
    private $maxUriLength = 2048;

    private $maxNumHeaders = 100;

    private $maxHeadersValue = 4096;

    private $maxHeaderValue = 2048;

    private $maxHeaderName = 64;

    public function testRequestUriNotTooLong()
    {
        $uri = str_pad('', $this->maxUriLength, '*');
        $request = new ServerRequest('GET', new Uri($uri), [], null, '1.1', ['REQUEST_URI'     => $uri]);

        $handler = function ($request) {
            return new Response();
        };

        $middleware = new RequestLimitationsMiddleware();
        $response = $middleware->process($request, new RequestHandlerCallable($handler));

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    /**
     * @expectedException Chiron\Http\Exception\Client\UriTooLongHttpException
     */
    public function testRequestUriTooLong()
    {
        $uri = str_pad('', $this->maxUriLength + 1, '*');
        $request = new ServerRequest('GET', new Uri($uri), [], null, '1.1', ['REQUEST_URI'     => $uri]);

        $handler = function ($request) {
            return new Response();
        };

        $middleware = new RequestLimitationsMiddleware();
        $middleware->process($request, new RequestHandlerCallable($handler));
    }

    /**
     * @expectedException Chiron\Http\Exception\Client\RequestHeaderFieldsTooLargeHttpException
     */
    public function testTooMuchHeaders()
    {
        $uri = '/';
        $request = new ServerRequest('GET', new Uri($uri), [], null, '1.1', ['REQUEST_URI'     => $uri]);

        $handler = function ($request) {
            return new Response();
        };

        for ($index = 1; $index <= $this->maxNumHeaders + 1; $index++) {
            $request = $request->withHeader('X-Custom_' . $index, ['TEST']);
        }

        $middleware = new RequestLimitationsMiddleware();
        $middleware->process($request, new RequestHandlerCallable($handler));
    }

    /**
     * @expectedException Chiron\Http\Exception\Client\RequestHeaderFieldsTooLargeHttpException
     */
    public function testAllHeadersTooBig()
    {
        $uri = '/';
        $request = new ServerRequest('GET', new Uri($uri), [], null, '1.1', ['REQUEST_URI'     => $uri]);

        $handler = function ($request) {
            return new Response();
        };

        for ($index = 1; $index <= 100; $index++) {
            $request = $request->withHeader('X-Custom_' . $index, [str_pad('', intval($this->maxHeadersValue / 100), '*')]);
        }

        $middleware = new RequestLimitationsMiddleware();
        $middleware->process($request, new RequestHandlerCallable($handler));
    }

    /**
     * @expectedException Chiron\Http\Exception\Client\RequestHeaderFieldsTooLargeHttpException
     */
    public function testOneHeaderTooBig()
    {
        $uri = '/';
        $request = new ServerRequest('GET', new Uri($uri), [], null, '1.1', ['REQUEST_URI'     => $uri]);

        $handler = function ($request) {
            return new Response();
        };

        $request = $request->withHeader('X-Very-Long-Header-Value', [str_pad('', $this->maxHeaderValue, '*')]);

        $middleware = new RequestLimitationsMiddleware();
        $middleware->process($request, new RequestHandlerCallable($handler));
    }

    /**
     * @expectedException Chiron\Http\Exception\Client\RequestHeaderFieldsTooLargeHttpException
     */
    public function testHeaderNameTooLong()
    {
        $uri = '/';
        $request = new ServerRequest('GET', new Uri($uri), [], null, '1.1', ['REQUEST_URI'     => $uri]);

        $handler = function ($request) {
            return new Response();
        };

        $request = $request->withHeader(str_pad('X-Very-Long-Header-Name', $this->maxHeaderName + 1, '*'), ['TEST']);

        $middleware = new RequestLimitationsMiddleware();
        $middleware->process($request, new RequestHandlerCallable($handler));
    }
}
