<?php

declare(strict_types=1);

namespace Tests\Handler\Stack;

use Chiron\Handler\Stack\Decorator\CallableMiddleware;
use Chiron\Handler\Stack\Decorator\CallableRequestHandlerDecorator;
use Chiron\Handler\Stack\RequestHandlerStack;
use Chiron\Http\Psr\Response;
use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class RequestHandlerStackTest extends TestCase
{
    public $request;

    protected function setUp()
    {
        $this->request = new ServerRequest('GET', new Uri('/'));
    }

    protected function tearDown()
    {
    }

    public function testStackHandlerConstructor()
    {
        $default = new CallableRequestHandlerDecorator(function ($request) {
            $response = new Response();
            $response->getBody()->write('1');

            return $response;
        });
        $middlewares = [
            new CallableMiddleware(function ($request, $handler) {
                $response = $handler->handle($request);
                $response->getBody()->write('3');

                return $response;
            }),
            new CallableMiddleware(function ($request, $handler) {
                $response = $handler->handle($request);
                $response->getBody()->write('2');

                return $response;
            }),
        ];

        $stack = new RequestHandlerStack($default, $middlewares);

        $response = $stack->handle($this->request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals('123', (string) $response->getBody());
    }

    public function testStackHandlerAppendMiddleware()
    {
        $default = new CallableRequestHandlerDecorator(function ($request) {
            $response = new Response();
            $response->getBody()->write('1');

            return $response;
        });

        $stack = new RequestHandlerStack($default, []);

        $stack->append(new CallableMiddleware(function ($request, $handler) {
            $response = $handler->handle($request);
            $response->getBody()->write('2');

            return $response;
        }));

        $stack->append(new CallableMiddleware(function ($request, $handler) {
            $response = $handler->handle($request);
            $response->getBody()->write('3');

            return $response;
        }));

        $response = $stack->handle($this->request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals('123', (string) $response->getBody());
    }

    public function testStackHandlerPrependMiddleware()
    {
        $default = new CallableRequestHandler(function ($request) {
            $response = new Response();
            $response->getBody()->write('1');

            return $response;
        });

        $stack = new RequestHandlerStack($default, []);

        $stack->prepend(new CallableMiddleware(function ($request, $handler) {
            $response = $handler->handle($request);
            $response->getBody()->write('3');

            return $response;
        }));

        $stack->prepend(new CallableMiddleware(function ($request, $handler) {
            $response = $handler->handle($request);
            $response->getBody()->write('2');

            return $response;
        }));

        $response = $stack->handle($this->request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals('123', (string) $response->getBody());
    }

    public function testEmptyMiddlewareArray()
    {
        $default = new CallableRequestHandler(function ($request) {
            $response = new Response();
            $response->getBody()->write('EMPTY');

            return $response;
        });

        $stack = new RequestHandlerStack($default, []);
        $response = $stack->handle($this->request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals('EMPTY', (string) $response->getBody());
    }

    public function testMiddlewareException()
    {
        $this->expectException('InvalidArgumentException');

        $default = new CallableRequestHandlerDecorator(function ($request) {
            $response = new Response();

            return $response;
        });

        $stack = new RequestHandlerStack($default, ['bad_parameter']);
        $response = $stack->handle($this->request);
    }
}
