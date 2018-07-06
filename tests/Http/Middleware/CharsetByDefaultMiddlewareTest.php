<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Middleware;

use Chiron\Http\Middleware\CharsetByDefaultMiddleware;
use ErrorException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Chiron\Http\Psr\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Http\Factory\ServerRequestFactory;
use Chiron\Tests\Utils\HandlerProxy2;
use InvalidArgumentException;

class CharsetByDefaultMiddlewareTest extends TestCase
{
    public function testDefaultContentTypeAndDefaultCharset()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $handler = function ($request) {
            return new Response();
        };
        $middleware = new CharsetByDefaultMiddleware();
        $response = $middleware->process($request, new HandlerProxy2($handler));

        $this->assertEquals('text/html; charset=utf-8', $response->getHeaderLine('Content-Type'));
    }

    public function testDefaultContentTypeAndWithDefinedCharset()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $handler = function ($request) {
            return new Response();
        };
        $middleware = new CharsetByDefaultMiddleware('iso-8859-1');
        $response = $middleware->process($request, new HandlerProxy2($handler));

        $this->assertEquals('text/html; charset=iso-8859-1', $response->getHeaderLine('Content-Type'));
    }

    public function testWithTextualContentType()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $handler = function ($request) {
            $response = new Response();
            $response = $response->withHeader('Content-Type', 'text/plain; boundary=something');
            return $response;
        };
        $middleware = new CharsetByDefaultMiddleware('iso-8859-1');
        $response = $middleware->process($request, new HandlerProxy2($handler));

        $this->assertEquals('text/plain; boundary=something; charset=iso-8859-1', $response->getHeaderLine('Content-Type'));
    }

    public function testWithNonTextualContentType()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $handler = function ($request) {
            $response = new Response();
            $response = $response->withHeader('Content-Type', 'application/pdf');
            return $response;
        };
        $middleware = new CharsetByDefaultMiddleware('iso-8859-1');
        $response = $middleware->process($request, new HandlerProxy2($handler));

        $this->assertEquals('application/pdf', $response->getHeaderLine('Content-Type'));
    }

    public function testWithNonTextualContentTypeButWhitlisted()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $handler = function ($request) {
            $response = new Response();
            $response = $response->withHeader('Content-Type', 'application/json');
            return $response;
        };
        $middleware = new CharsetByDefaultMiddleware('iso-8859-1');
        $response = $middleware->process($request, new HandlerProxy2($handler));

        $this->assertEquals('application/json; charset=iso-8859-1', $response->getHeaderLine('Content-Type'));
    }

    public function testWithNonTextualContentTypeButWhitlistedAndWithParamInContentTypeHeader()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $handler = function ($request) {
            $response = new Response();
            $response = $response->withHeader('Content-Type', 'application/json; boundary=something');
            return $response;
        };
        $middleware = new CharsetByDefaultMiddleware('iso-8859-1');
        $response = $middleware->process($request, new HandlerProxy2($handler));

        $this->assertEquals('application/json; boundary=something; charset=iso-8859-1', $response->getHeaderLine('Content-Type'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testReturnsErrorResponseIfHandlerRaisesAnException_StartWithNumeric()
    {
        $middleware = new CharsetByDefaultMiddleware('123456-UTF');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testReturnsErrorResponseIfHandlerRaisesAnException_StringToShort()
    {
        $middleware = new CharsetByDefaultMiddleware('utf');
    }
}
