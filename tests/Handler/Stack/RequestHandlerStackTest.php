<?php

declare(strict_types=1);

namespace Tests\Handler\Stack;

use Chiron\Http\Factory\ServerRequestFactory;
use Chiron\Http\Psr\Response;
use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Stream;
use Chiron\Handler\Stack\RequestHandlerStack;
use Chiron\Handler\Stack\Decorator\CallableMiddlewareDecorator;
use Chiron\Handler\CallableRequestHandlerDecorator;
use Psr\Http\Message\ResponseInterface;
use PHPUnit\Framework\TestCase;

class RequestHandlerStackTest extends TestCase
{
    public $request;

    protected function setUp()
    {
        $this->request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
        ]);
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
            new CallableMiddlewareDecorator(function ($request, $handler) {
                $response = $handler->handle($request);
                $response->getBody()->write('3');
                return $response;
            }),
            new CallableMiddlewareDecorator(function ($request, $handler) {
                $response = $handler->handle($request);
                $response->getBody()->write('2');
                return $response;
            })
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

        $stack->append(new CallableMiddlewareDecorator(function ($request, $handler) {
                $response = $handler->handle($request);
                $response->getBody()->write('2');
                return $response;
            }));

        $stack->append(new CallableMiddlewareDecorator(function ($request, $handler) {
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
        $default = new CallableRequestHandlerDecorator(function ($request) {
            $response = new Response();
            $response->getBody()->write('1');
            return $response;
        });

        $stack = new RequestHandlerStack($default, []);

        $stack->prepend(new CallableMiddlewareDecorator(function ($request, $handler) {
                $response = $handler->handle($request);
                $response->getBody()->write('3');
                return $response;
            }));

        $stack->prepend(new CallableMiddlewareDecorator(function ($request, $handler) {
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
        $default = new CallableRequestHandlerDecorator(function ($request) {
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
