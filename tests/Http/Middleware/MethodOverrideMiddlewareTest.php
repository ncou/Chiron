<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Middleware;

use Chiron\Http\Middleware\MethodOverrideMiddleware;
use Chiron\Http\Psr\Response;
use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;
use Chiron\Tests\Utils\RequestHandlerCallable;
use PHPUnit\Framework\TestCase;

class MethodOverrideMiddlewareTest extends TestCase
{
    private $middleware;

    /**
     * Setup.
     */
    protected function setUp()
    {
        $this->middleware = new MethodOverrideMiddleware();
    }

    public function testGETMethodOverride()
    {
        $request = new ServerRequest('GET', new Uri('/'));
        $request = $request->withQueryParams(['_method' => 'POST']);

        $handler = function ($request) {
            $this->assertEquals('POST', $request->getMethod());

            return new Response();
        };
        $this->middleware->process($request, new RequestHandlerCallable($handler));
    }

    public function testGETMethodOverrideWithCaseSensitive()
    {
        $request = new ServerRequest('GET', new Uri('/'));
        $request = $request->withQueryParams(['_method' => 'PosT']);

        $handler = function ($request) {
            $this->assertEquals('PosT', $request->getMethod());

            return new Response();
        };
        $this->middleware->process($request, new RequestHandlerCallable($handler));
    }

    public function testPOSTMethodOverride()
    {
        $request = new ServerRequest('POST', new Uri('/'));
        $request = $request->withParsedBody(['_method' => 'GET']);

        $handler = function ($request) {
            $this->assertEquals('GET', $request->getMethod());

            return new Response();
        };
        $this->middleware->process($request, new RequestHandlerCallable($handler));
    }

    public function testPOSTMethodOverrideCaseSensitive()
    {
        $request = new ServerRequest('POST', new Uri('/'));
        $request = $request->withParsedBody(['_method' => 'GeT']);

        $handler = function ($request) {
            $this->assertEquals('GeT', $request->getMethod());

            return new Response();
        };
        $this->middleware->process($request, new RequestHandlerCallable($handler));
    }

    public function testHeaderMethodOverride()
    {
        $request = new ServerRequest('GET', new Uri('/'));
        $request = $request->withHeader('X-Http-Method-Override', 'PUT');

        $handler = function ($request) {
            $this->assertEquals('PUT', $request->getMethod());

            return new Response();
        };
        $this->middleware->process($request, new RequestHandlerCallable($handler));
    }

    public function testHeaderMethodOverrideCaseSensitive()
    {
        $request = new ServerRequest('GET', new Uri('/'));
        $request = $request->withHeader('X-Http-Method-Override', 'PuT');

        $handler = function ($request) {
            $this->assertEquals('PuT', $request->getMethod());

            return new Response();
        };
        $this->middleware->process($request, new RequestHandlerCallable($handler));
    }
}
