<?php

declare(strict_types=1);

namespace Chiron\Tests\Middleware;

use Chiron\Http\Factory\ServerRequestFactory;
use Chiron\Http\Response;
use Chiron\Middleware\EmitterMiddleware;
use Chiron\Tests\Utils\HandlerProxy2;
use Chiron\Tests\Utils\HeaderStack;
use PHPUnit\Framework\TestCase;

class EmitterMiddlewareTest extends TestCase
{
    private $middleware;

    /**
     * Setup.
     */
    protected function setUp()
    {
        $this->middleware = new EmitterMiddleware();
        HeaderStack::reset();
    }

    protected function tearDown()
    {
        HeaderStack::reset();
    }

    public function testEmitsMessageBody(): void
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $handler = function ($request) {
            $response = (new Response())
                ->withStatus(200)
                ->withAddedHeader('Content-Type', 'text/plain');
            $response->getBody()->write('Content!');

            return $response;
        };

        $this->expectOutputString('Content!');

        $this->middleware->process($request, new HandlerProxy2($handler));

        //$this->emitter->emit($response);
    }

    public function testEmitsResponseHeaders(): void
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $handler = function ($request) {
            $response = (new Response())
                ->withStatus(200)
                ->withAddedHeader('Content-Type', 'text/plain');
            $response->getBody()->write('Content!');

            return $response;
        };

        ob_start();
        $this->middleware->process($request, new HandlerProxy2($handler));
        ob_end_clean();

        self::assertTrue(HeaderStack::has('HTTP/1.1 200 OK'));
        self::assertTrue(HeaderStack::has('Content-Type: text/plain'));
    }

    public function testMultipleSetCookieHeadersAreNotReplaced(): void
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $handler = function ($request) {
            $response = (new Response())
            ->withStatus(200)
            ->withAddedHeader('Set-Cookie', 'foo=bar')
            ->withAddedHeader('Set-Cookie', 'bar=baz');

            return $response;
        };

        $response = $this->middleware->process($request, new HandlerProxy2($handler));

        $expectedStack = [
            ['header' => 'Set-Cookie: foo=bar', 'replace' => false, 'status_code' => 200],
            ['header' => 'Set-Cookie: bar=baz', 'replace' => false, 'status_code' => 200],
            ['header' => 'HTTP/1.1 200 OK', 'replace' => true, 'status_code' => 200],
        ];
        self::assertSame($expectedStack, HeaderStack::stack());
    }

    public function testDoesNotLetResponseCodeBeOverriddenByPHP(): void
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $handler = function ($request) {
            $response = (new Response())
            ->withStatus(202)
            ->withAddedHeader('Location', 'http://api.my-service.com/12345678')
            ->withAddedHeader('Content-Type', 'text/plain');

            return $response;
        };

        $this->middleware->process($request, new HandlerProxy2($handler));

        $expectedStack = [
            ['header' => 'Location: http://api.my-service.com/12345678', 'replace' => false, 'status_code' => 202],
            ['header' => 'Content-Type: text/plain', 'replace' => false, 'status_code' => 202],
            ['header' => 'HTTP/1.1 202 Accepted', 'replace' => true, 'status_code' => 202],
        ];
        self::assertSame($expectedStack, HeaderStack::stack());
    }

    public function testEmitterRespectLocationHeader(): void
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $handler = function ($request) {
            $response = (new Response())
            ->withStatus(200)
            ->withAddedHeader('Location', 'http://api.my-service.com/12345678');

            return $response;
        };

        $this->middleware->process($request, new HandlerProxy2($handler));

        $expectedStack = [
            ['header' => 'Location: http://api.my-service.com/12345678', 'replace' => false, 'status_code' => 200],
            ['header' => 'HTTP/1.1 200 OK', 'replace' => true, 'status_code' => 200],
        ];
        self::assertSame($expectedStack, HeaderStack::stack());
    }
}
