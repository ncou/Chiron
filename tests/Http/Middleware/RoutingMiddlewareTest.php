<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Middleware;

use Chiron\Handler\Stack\RequestHandlerStack;
use Chiron\Http\Factory\ServerRequestFactory;
use Chiron\Http\Middleware\DispatcherMiddleware;
//use Psr\Http\Server\MiddlewareInterface;
use Chiron\Http\Middleware\RoutingMiddleware;
//use Prophecy\Prophecy\ObjectProphecy;
use Chiron\Http\Psr\Response;
use Chiron\Routing\Router;
use Chiron\Tests\Utils\HandlerProxy2;
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
        $requestHandler = new RequestHandlerStack(new HandlerProxy2($this->empty));
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/foo',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $handler = function ($request) {
            $response = (new Response(200))->withHeader('X-Custom', 'foobar');
            $response->getBody()->write('Found!');

            return $response;
        };

        $router = new Router();
        $router->map('/foo', new HandlerProxy2($handler))->method('GET');

        $middlewareRouting = new RoutingMiddleware($router);
        $middlewareDispatcher = new DispatcherMiddleware();

        $requestHandler->prepend($middlewareRouting);
        $requestHandler->prepend($middlewareDispatcher);

        //$response = $middleware->process($request, new HandlerProxy2($this->empty));
        $response = $requestHandler->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('X-Custom'));
        $this->assertSame('Found!', (string) $response->getBody());
    }

    public function testRouteFoundWithAttributes()
    {
        $requestHandler = new RequestHandlerStack(new HandlerProxy2($this->empty));
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/foo/123456/',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $handler = function ($request) {
            $id = $request->getAttribute('id');
            $response = new Response(200);
            $response->getBody()->write('Found! id=' . $id);

            return $response;
        };

        $router = new Router();
        $router->map('/foo/[i:id]/', new HandlerProxy2($handler))->method('GET');

        $middlewareRouting = new RoutingMiddleware($router);
        $middlewareDispatcher = new DispatcherMiddleware();

        $requestHandler->prepend($middlewareRouting);
        $requestHandler->prepend($middlewareDispatcher);

        //$response = $middleware->process($request, new HandlerProxy2($this->empty));
        $response = $requestHandler->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame('Found! id=123456', (string) $response->getBody());
    }

    public function testRouteFoundWithoutBodyFromHEADMethod()
    {
        $requestHandler = new RequestHandlerStack(new HandlerProxy2($this->empty));
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/foo',
            'REQUEST_METHOD'         => 'HEAD',
        ]);

        $handler = function ($request) {
            $response = (new Response(200))->withHeader('X-Custom', 'foobar');
            $response->getBody()->write('Found!');

            return $response;
        };

        $router = new Router();
        $router->map('/foo', new HandlerProxy2($handler))->method('GET');

        $middlewareRouting = new RoutingMiddleware($router);
        $middlewareDispatcher = new DispatcherMiddleware();

        $requestHandler->prepend($middlewareRouting);
        $requestHandler->prepend($middlewareDispatcher);

        //$response = $middleware->process($request, new HandlerProxy2($this->empty));
        $response = $requestHandler->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('X-Custom'));
        $this->assertSame('', (string) $response->getBody());
    }

    public function testRouteFoundWithoutBodyFromHEADMethodWithCustomHandler()
    {
        $requestHandler = new RequestHandlerStack(new HandlerProxy2($this->empty));
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/foo',
            'REQUEST_METHOD'         => 'HEAD',
        ]);

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
        $router->map('/foo', new HandlerProxy2($handler))->method('GET');
        $router->map('/foo', new HandlerProxy2($handlerCustom))->method('HEAD');

        $middlewareRouting = new RoutingMiddleware($router);
        $middlewareDispatcher = new DispatcherMiddleware();

        $requestHandler->prepend($middlewareRouting);
        $requestHandler->prepend($middlewareDispatcher);

        //$response = $middleware->process($request, new HandlerProxy2($this->empty));
        $response = $requestHandler->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('X-Custom-HEAD'));
        $this->assertSame('', (string) $response->getBody());
    }

    public function testRouteFoundWithAllowHeaderForOPTIONSMethod()
    {
        $requestHandler = new RequestHandlerStack(new HandlerProxy2($this->empty));
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/foo',
            'REQUEST_METHOD'         => 'OPTIONS',
        ]);

        $handler = function ($request) {
            $response = (new Response(200))->withHeader('X-Custom', 'foobar');
            $response->getBody()->write('Found!');

            return $response;
        };

        $router = new Router();
        $router->map('/foo', new HandlerProxy2($handler))->method('GET');

        $middlewareRouting = new RoutingMiddleware($router);
        $middlewareDispatcher = new DispatcherMiddleware();

        $requestHandler->prepend($middlewareRouting);
        $requestHandler->prepend($middlewareDispatcher);

        //$response = $middleware->process($request, new HandlerProxy2($this->empty));
        $response = $requestHandler->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertFalse($response->hasHeader('X-Custom'));
        $this->assertTrue($response->hasHeader('Allow'));
        $this->assertSame('OPTIONS, GET', $response->getHeaderLine('Allow'));
        $this->assertSame('', (string) $response->getBody());
    }

    public function testRouteFoundWithCustomHandlerForOPTIONSMethod()
    {
        $requestHandler = new RequestHandlerStack(new HandlerProxy2($this->empty));
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/foo',
            'REQUEST_METHOD'         => 'OPTIONS',
        ]);

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
        $router->map('/foo', new HandlerProxy2($handler))->method('GET');
        $router->map('/foo', new HandlerProxy2($handlerCustom))->method('OPTIONS');

        $middlewareRouting = new RoutingMiddleware($router);
        $middlewareDispatcher = new DispatcherMiddleware();

        $requestHandler->prepend($middlewareRouting);
        $requestHandler->prepend($middlewareDispatcher);

        //$response = $middleware->process($request, new HandlerProxy2($this->empty));
        $response = $requestHandler->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('X-Custom'));
        $this->assertSame('Custom Handler for OPTIONS!', (string) $response->getBody());
    }

    /**
     * @expectedException \Chiron\Http\Exception\NotFoundHttpException
     */
    public function testRouteNotFound()
    {
        $requestHandler = new RequestHandlerStack(new HandlerProxy2($this->empty));
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/foobar',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $handler = function ($request) {
            $response = (new Response(200))->withHeader('X-Custom', 'foobar');
            $response->getBody()->write('Found!');

            return $response;
        };

        $router = new Router();
        $router->map('/foo', new HandlerProxy2($handler))->method('GET');

        $middlewareRouting = new RoutingMiddleware($router);
        $middlewareDispatcher = new DispatcherMiddleware();

        $requestHandler->prepend($middlewareRouting);
        $requestHandler->prepend($middlewareDispatcher);

        $response = $requestHandler->handle($request);
    }

    /**
     * @expectedException \Chiron\Http\Exception\MethodNotAllowedHttpException
     */
    public function testRouteMethodNotAllowed()
    {
        $requestHandler = new RequestHandlerStack(new HandlerProxy2($this->empty));
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/foo',
            'REQUEST_METHOD'         => 'PUT',
        ]);

        $handler = function ($request) {
            $response = (new Response(200))->withHeader('X-Custom', 'foobar');
            $response->getBody()->write('Found!');

            return $response;
        };

        $router = new Router();
        $router->map('/foo', new HandlerProxy2($handler))->method('POST');

        $middlewareRouting = new RoutingMiddleware($router);
        $middlewareDispatcher = new DispatcherMiddleware();

        $requestHandler->prepend($middlewareRouting);
        $requestHandler->prepend($middlewareDispatcher);

        $response = $requestHandler->handle($request);
    }
}
