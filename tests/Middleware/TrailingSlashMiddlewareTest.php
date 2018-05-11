<?php

declare(strict_types=1);

namespace Chiron\Tests\Middleware;

use Chiron\Http\Factory\ServerRequestFactory;
use Chiron\Http\Response;
use Chiron\Middleware\TrailingSlashMiddleware;
use Chiron\Tests\Utils\HandlerProxy2;
use PHPUnit\Framework\TestCase;

class TrailingSlashMiddlewareTest extends TestCase
{
    public function removeProvider(): array
    {
        return [
            ['/foo/bar', '/foo/bar'],
            ['/foo/bar/', '/foo/bar'],
            ['/', '/'],
            ['', '/'],
        ];
    }
    /**
     * @dataProvider removeProvider
     */
    public function testRemove(string $uri, string $result)
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => $uri,
            'REQUEST_METHOD'         => 'GET',
        ]);

        $middleware = new TrailingSlashMiddleware();
        $handler = function ($request) use (&$path) {
            $path = $request->getUri()->getPath();
            return new Response();
        };

        $middleware->process($request, new HandlerProxy2($handler));

        $this->assertEquals($result, $path);
    }
    public function addProvider(): array
    {
        return [
            ['/foo/bar', '/foo/bar/'],
            ['/foo/bar/', '/foo/bar/'],
            ['/', '/'],
            ['', '/'],
            ['/index.html', '/index.html'],
            ['/index', '/index/'],
        ];
    }
    /**
     * @dataProvider addProvider
     */
    public function testAdd(string $uri, string $result)
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => $uri,
            'REQUEST_METHOD'         => 'GET',
        ]);

        $middleware = new TrailingSlashMiddleware(true);
        $handler = function ($request) use (&$path) {
            $path = $request->getUri()->getPath();
            return new Response();
        };

        $middleware->process($request, new HandlerProxy2($handler));

        $this->assertEquals($result, $path);
    }
    public function testRedirect()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/foo/bar/',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $middleware = (new TrailingSlashMiddleware())->redirect(true);
        $handler = function ($request) {
            return new Response();
        };

        $response = $middleware->process($request, new HandlerProxy2($handler));

        $this->assertEquals(301, (string) $response->getStatusCode());
        $this->assertEquals('/foo/bar', $response->getHeaderLine('Location'));
    }
}
