<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Middleware;

use Chiron\Http\Middleware\RequestIdMiddleware;
use Chiron\Http\Psr\Response;
use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;
use Chiron\Tests\Utils\RequestHandlerCallable;
use PHPUnit\Framework\TestCase;

class RequestIdMiddlewareTest extends TestCase
{
    private $middleware;

    /**
     * Setup.
     */
    protected function setUp()
    {
        $this->middleware = new RequestIdMiddleware();
    }

    public function testRequestIdIsAnObject()
    {
        $this->assertThat(
            method_exists($this->middleware, 'process'),
            $this->isTrue(),
            'A middleware must have a handle method.'
        );
    }

    public function testRequestIdShouldBeFilledIfDoesNotExistInRequestAndResponse()
    {
        $request = new ServerRequest('GET', new Uri('/'));
        $handler = function ($request) use (&$uuid) {
            $uuid = $request->getHeaderLine('X-Request-Id');
            $this->assertNotEmpty($uuid);

            return new Response();
        };
        $response = $this->middleware->process($request, new RequestHandlerCallable($handler));

        $this->assertEquals(
            $response->getHeaderLine('X-Request-Id'),
            $uuid,
            'The same X-Request-Id must be set in request and response.'
        );
    }

    public function testPropagateRequestIdToResponseIfProvidedInRequest()
    {
        $request = new ServerRequest('GET', new Uri('/'));
        $request = $request->withHeader('X-Request-Id', '09226165-364a-461a-bf5c-e859d70d907e');
        $handler = function ($request) {
            $this->assertEquals(
                '09226165-364a-461a-bf5c-e859d70d907e',
                $request->getHeaderLine('X-Request-Id'),
                'The Request header must not be modified.'
            );

            return new Response();
        };
        $response = $this->middleware->process($request, new RequestHandlerCallable($handler));
        $this->assertEquals(
            '09226165-364a-461a-bf5c-e859d70d907e',
            $response->getHeaderLine('X-Request-Id'),
            'The request X-Request-ID header must be set in the response.'
        );
    }
}
