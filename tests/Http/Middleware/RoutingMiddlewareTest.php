<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Middleware;

use Chiron\Pipe\Pipeline;
use Chiron\Http\Middleware\DispatcherMiddleware;
//use Psr\Http\Server\MiddlewareInterface;
use Chiron\Http\Middleware\RoutingMiddleware;
//use Prophecy\Prophecy\ObjectProphecy;
use Chiron\Http\Psr\Response;
use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;
use Chiron\Routing\Router;
use Chiron\Tests\Utils\RequestHandlerCallable;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RoutingMiddlewareTest extends TestCase
{
    /** @var RouterInterface|ObjectProphecy */
    private $router;

    /** @var ResponseInterface|ObjectProphecy */
    private $response;

    /** @var RouteMiddleware */
    private $middleware;

    /** @var ServerRequestInterface|ObjectProphecy */
    private $request;

    /** @var RequestHandlerInterface|ObjectProphecy */
    private $handler;

    protected function setUp()
    {
        $this->empty = function ($request) {
            return new Response(204);
        };
    }

    public function testRouteFound()
    {
        $pipeline = new Pipeline(new RequestHandlerCallable($this->empty));
        $request = new ServerRequest('GET', new Uri('/foo'));

        $handler = function ($request) {
            $response = (new Response(200))->withHeader('X-Custom', 'foobar');
            $response->getBody()->write('Found!');

            return $response;
        };

        $router = new Router();
        $router->map('/foo', new RequestHandlerCallable($handler))->method('GET');

        $middlewareRouting = new RoutingMiddleware($router);
        $middlewareDispatcher = new DispatcherMiddleware();

        $pipeline->prepend($middlewareRouting);
        $pipeline->prepend($middlewareDispatcher);

        //$response = $middleware->process($request, new RequestHandlerCallable($this->empty));
        $response = $pipeline->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('X-Custom'));
        $this->assertSame('Found!', (string) $response->getBody());
    }

    public function testRouteFoundWithAttributes()
    {
        $pipeline = new Pipeline(new RequestHandlerCallable($this->empty));
        $request = new ServerRequest('GET', new Uri('/foo/123456/'));

        $handler = function ($request) {
            $id = $request->getAttribute('id');
            $response = new Response(200);
            $response->getBody()->write('Found! id=' . $id);

            return $response;
        };

        $router = new Router();
        $router->map('/foo/[i:id]/', new RequestHandlerCallable($handler))->method('GET');

        $middlewareRouting = new RoutingMiddleware($router);
        $middlewareDispatcher = new DispatcherMiddleware();

        $pipeline->prepend($middlewareRouting);
        $pipeline->prepend($middlewareDispatcher);

        //$response = $middleware->process($request, new RequestHandlerCallable($this->empty));
        $response = $pipeline->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame('Found! id=123456', (string) $response->getBody());
    }

    public function testRouteFoundWithoutBodyFromHEADMethod()
    {
        $pipeline = new Pipeline(new RequestHandlerCallable($this->empty));
        $request = new ServerRequest('HEAD', new Uri('/foo'));

        $handler = function ($request) {
            $response = (new Response(200))->withHeader('X-Custom', 'foobar');
            $response->getBody()->write('Found!');

            return $response;
        };

        $router = new Router();
        $router->map('/foo', new RequestHandlerCallable($handler))->method('GET');

        $middlewareRouting = new RoutingMiddleware($router);
        $middlewareDispatcher = new DispatcherMiddleware();

        $pipeline->prepend($middlewareRouting);
        $pipeline->prepend($middlewareDispatcher);

        //$response = $middleware->process($request, new RequestHandlerCallable($this->empty));
        $response = $pipeline->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('X-Custom'));
        $this->assertSame('', (string) $response->getBody());
    }

    public function testRouteFoundWithoutBodyFromHEADMethodWithCustomHandler()
    {
        $pipeline = new Pipeline(new RequestHandlerCallable($this->empty));
        $request = new ServerRequest('HEAD', new Uri('/foo'));

        $handler = function ($request) {
            $response = (new Response(200))->withHeader('X-Custom', 'foobar');
            $response->getBody()->write('Found!');

            return $response;
        };
        $handlerCustom = function ($request) {
            $response = (new Response(200))->withHeader('X-Custom-HEAD', 'bar');
            $response->getBody()->write('Custom Handler for HEAD!');

            return $response;
        };

        $router = new Router();
        $router->map('/foo', new RequestHandlerCallable($handler))->method('GET');
        $router->map('/foo', new RequestHandlerCallable($handlerCustom))->method('HEAD');

        $middlewareRouting = new RoutingMiddleware($router);
        $middlewareDispatcher = new DispatcherMiddleware();

        $pipeline->prepend($middlewareRouting);
        $pipeline->prepend($middlewareDispatcher);

        //$response = $middleware->process($request, new RequestHandlerCallable($this->empty));
        $response = $pipeline->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('X-Custom-HEAD'));
        $this->assertSame('', (string) $response->getBody());
    }

    public function testRouteFoundWithAllowHeaderForOPTIONSMethod()
    {
        $pipeline = new Pipeline(new RequestHandlerCallable($this->empty));
        $request = new ServerRequest('OPTIONS', new Uri('/foo'));

        $handler = function ($request) {
            $response = (new Response(200))->withHeader('X-Custom', 'foobar');
            $response->getBody()->write('Found!');

            return $response;
        };

        $router = new Router();
        $router->map('/foo', new RequestHandlerCallable($handler))->method('GET');

        $middlewareRouting = new RoutingMiddleware($router);
        $middlewareDispatcher = new DispatcherMiddleware();

        $pipeline->prepend($middlewareRouting);
        $pipeline->prepend($middlewareDispatcher);

        //$response = $middleware->process($request, new RequestHandlerCallable($this->empty));
        $response = $pipeline->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertFalse($response->hasHeader('X-Custom'));
        $this->assertTrue($response->hasHeader('Allow'));
        $this->assertSame('OPTIONS, GET', $response->getHeaderLine('Allow'));
        $this->assertSame('', (string) $response->getBody());
    }

    public function testRouteFoundWithCustomHandlerForOPTIONSMethod()
    {
        $pipeline = new Pipeline(new RequestHandlerCallable($this->empty));
        $request = new ServerRequest('OPTIONS', new Uri('/foo'));

        $handler = function ($request) {
            $response = (new Response(200))->withHeader('X-Custom', 'foobar');
            $response->getBody()->write('Found!');

            return $response;
        };

        $handlerCustom = function ($request) {
            $response = (new Response(200))->withHeader('X-Custom', 'foobar');
            $response->getBody()->write('Custom Handler for OPTIONS!');

            return $response;
        };

        $router = new Router();
        $router->map('/foo', new RequestHandlerCallable($handler))->method('GET');
        $router->map('/foo', new RequestHandlerCallable($handlerCustom))->method('OPTIONS');

        $middlewareRouting = new RoutingMiddleware($router);
        $middlewareDispatcher = new DispatcherMiddleware();

        $pipeline->prepend($middlewareRouting);
        $pipeline->prepend($middlewareDispatcher);

        //$response = $middleware->process($request, new RequestHandlerCallable($this->empty));
        $response = $pipeline->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('X-Custom'));
        $this->assertSame('Custom Handler for OPTIONS!', (string) $response->getBody());
    }

    /**
     * @expectedException \Chiron\Http\Exception\Client\NotFoundHttpException
     */
    public function testRouteNotFound()
    {
        $pipeline = new Pipeline(new RequestHandlerCallable($this->empty));
        $request = new ServerRequest('GET', new Uri('/foobar'));

        $handler = function ($request) {
            $response = (new Response(200))->withHeader('X-Custom', 'foobar');
            $response->getBody()->write('Found!');

            return $response;
        };

        $router = new Router();
        $router->map('/foo', new RequestHandlerCallable($handler))->method('GET');

        $middlewareRouting = new RoutingMiddleware($router);
        $middlewareDispatcher = new DispatcherMiddleware();

        $pipeline->prepend($middlewareRouting);
        $pipeline->prepend($middlewareDispatcher);

        $response = $pipeline->handle($request);
    }

    /**
     * @expectedException \Chiron\Http\Exception\Client\MethodNotAllowedHttpException
     */
    public function testRouteMethodNotAllowed()
    {
        $pipeline = new Pipeline(new RequestHandlerCallable($this->empty));
        $request = new ServerRequest('PUT', new Uri('/foo'));

        $handler = function ($request) {
            $response = (new Response(200))->withHeader('X-Custom', 'foobar');
            $response->getBody()->write('Found!');

            return $response;
        };

        $router = new Router();
        $router->map('/foo', new RequestHandlerCallable($handler))->method('POST');

        $middlewareRouting = new RoutingMiddleware($router);
        $middlewareDispatcher = new DispatcherMiddleware();

        $pipeline->prepend($middlewareRouting);
        $pipeline->prepend($middlewareDispatcher);

        $response = $pipeline->handle($request);
    }
}
