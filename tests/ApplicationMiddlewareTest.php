<?php

declare(strict_types=1);

namespace Chiron\Tests\Middleware;

use Chiron\Application;
use Chiron\Handler\Stack\Decorator\CallableMiddlewareDecorator;
use Chiron\Http\Factory\ServerRequestFactory;
use Chiron\Http\Psr\Response;
use PHPUnit\Framework\TestCase;

class ApplicationMiddlewareTest extends TestCase
{
    /********************************************************************************
     * Middleware - Application
     *******************************************************************************/

    public function testApplicationWithoutMiddleware()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/foo',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $app = new Application();
        $response = $app->process($request);

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals('', (string) $response->getBody());
    }

    public function testMiddlewareWithMiddlewareInterface()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/foo',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $callable = function ($request, $handler) {
            return (new Response())->write('MIDDLEWARE');
        };
        $middleware = new CallableMiddlewareDecorator($callable);

        $app = new Application();
        $app->middleware($middleware);

        $response = $app->process($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('MIDDLEWARE', (string) $response->getBody());
    }

    public function testMiddlewareWithCallable()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/foo',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $callable = function ($request, $handler) {
            return (new Response())->write('MIDDLEWARE');
        };

        $app = new Application();
        $app->middleware($callable);

        $response = $app->process($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('MIDDLEWARE', (string) $response->getBody());
    }

    /**
     * @expectedException \Chiron\Container\Exception\EntryNotFoundException
     * @expectedExceptionMessage Identifier "MiddlewareNotPresentInTheContainer" is not defined in the container.
     */
    public function testMiddlewareWithStringNotPresentInContainer()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/foo',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $app = new Application();
        $app->middleware('MiddlewareNotPresentInTheContainer');

        $response = $app->process($request);
    }

    public function testMiddlewareWithStringInContainer()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/foo',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $entry = function ($c) {
            $callable = function ($request, $handler) {
                return (new Response())->write('MIDDLEWARE');
            };

            return new CallableMiddlewareDecorator($callable);
        };

        $app = new Application();
        $app->getContainer()->set('MiddlewareCallableInContainer', $entry);

        $app->middleware('MiddlewareCallableInContainer');

        $response = $app->process($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('MIDDLEWARE', (string) $response->getBody());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Middleware "integer" is neither a string service name, a PHP callable, or a Psr\Http\Server\MiddlewareInterface instance
     */
    public function testMiddlewareWithInvalidMiddleware()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/foo',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $app = new Application();

        $app->middleware(123456);

        $response = $app->process($request);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The middleware present in the container should be a Psr\Http\Server\MiddlewareInterface instance
     */
    public function testMiddlewareWithInvalidMiddlewareInContainer()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/foo',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $badEntry = function ($c) {
            return 123456;
        };

        $app = new Application();
        $app->getContainer()->set('BadMiddlewareType', $badEntry);

        $app->middleware('BadMiddlewareType');

        $response = $app->process($request);
    }

    public function testMiddlewareWithArrayOfMiddlewareInterface()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/foo',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $callable1 = function ($request, $handler) {
            $response = $handler->handle($request);

            return $response->write('MIDDLEWARE_1');
        };
        $middleware1 = new CallableMiddlewareDecorator($callable1);
        //---
        $callable2 = function ($request, $handler) {
            $response = new Response();

            return $response->write('MIDDLEWARE_2_');
        };
        $middleware2 = new CallableMiddlewareDecorator($callable2);

        $app = new Application();
        $app->middleware([$middleware1, $middleware2]);

        $response = $app->process($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('MIDDLEWARE_2_MIDDLEWARE_1', (string) $response->getBody());
    }

    public function testMiddlewareWithArrayOfCallable()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/foo',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $callable1 = function ($request, $handler) {
            $response = $handler->handle($request);

            return $response->write('MIDDLEWARE_1');
        };
        //---
        $callable2 = function ($request, $handler) {
            $response = new Response();

            return $response->write('MIDDLEWARE_2_');
        };

        $app = new Application();
        $app->middleware([$callable1, $callable2]);

        $response = $app->process($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('MIDDLEWARE_2_MIDDLEWARE_1', (string) $response->getBody());
    }

    public function testMiddlewareWithArrayOfString()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/foo',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $entry1 = function ($c) {
            $callable1 = function ($request, $handler) {
                $response = $handler->handle($request);
                $response->write('MIDDLEWARE_1');

                return $response;
            };

            return new CallableMiddlewareDecorator($callable1);
        };
        //---
        $entry2 = function ($c) {
            $callable2 = function ($request, $handler) {
                $response = new Response();
                $response->write('MIDDLEWARE_2_');

                return $response;
            };

            return new CallableMiddlewareDecorator($callable2);
        };

        $app = new Application();
        $app->getContainer()->set('ENTRY_1', $entry1);
        $app->getContainer()->set('ENTRY_2', $entry2);

        $app->middleware(['ENTRY_1', 'ENTRY_2']);

        $response = $app->process($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('MIDDLEWARE_2_MIDDLEWARE_1', (string) $response->getBody());
    }

    /********************************************************************************
     * Middleware - Route
     *******************************************************************************/

    /********************************************************************************
     * Middleware - RouteGroup
     *******************************************************************************/
}
