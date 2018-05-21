<?php

declare(strict_types=1);

namespace Chiron\Tests\Middleware;

use Chiron\Http\Factory\StreamFactory;
use Chiron\Http\Factory\ServerRequestFactory;
use Chiron\Http\Response;
use Chiron\Middleware\ParsedBodyMiddleware;
use Chiron\Tests\Utils\HandlerProxy2;
use PHPUnit\Framework\TestCase;

class ParsedBodyMiddlewareTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->middleware = new ParsedBodyMiddleware();
        $this->request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'POST',
        ]);
    }

    /*******************************************************************************
     * Body
     ******************************************************************************/
    public function testGetParsedBodyForm()
    {
        $request = $this->request->withHeader('Content-Type', 'application/x-www-form-urlencoded ;charset=utf8');
        $body = StreamFactory::createFromStringOrResource('php://temp', 'rw+');
        $body->write('foo=bar');
        $request = $request->withBody($body);

        $handler = function ($request) use (&$parsedBody) {
            $parsedBody = $request->getParsedBody();

            return new Response();
        };
        $this->middleware->process($request, new HandlerProxy2($handler));

        $this->assertEquals(['foo' => 'bar'], $parsedBody);
    }

    public function testGetParsedBodyJson()
    {
        $request = $this->request->withHeader('Content-Type', 'application/json ;charset=utf8');
        $body = StreamFactory::createFromStringOrResource('php://temp', 'rw+');
        $body->write('{"foo":"bar"}');
        $request = $request->withBody($body);

        $handler = function ($request) use (&$parsedBody) {
            $parsedBody = $request->getParsedBody();

            return new Response();
        };
        $this->middleware->process($request, new HandlerProxy2($handler));

        $this->assertEquals(['foo' => 'bar'], $parsedBody);
    }

    public function testGetParsedBodyInvalidJson()
    {
        $request = $this->request->withHeader('Content-Type', 'application/json ;charset=utf8');
        $body = StreamFactory::createFromStringOrResource('php://temp', 'rw+');
        $body->write('{foo}bar');
        $request = $request->withBody($body);

        $handler = function ($request) use (&$parsedBody) {
            $parsedBody = $request->getParsedBody();

            return new Response();
        };
        $this->middleware->process($request, new HandlerProxy2($handler));

        $this->assertNull($parsedBody);
    }

    public function testGetParsedBodySemiValidJson()
    {
        $request = $this->request->withHeader('Content-Type', 'application/json ;charset=utf8');
        $body = StreamFactory::createFromStringOrResource('php://temp', 'rw+');
        $body->write('"foo bar"');
        $request = $request->withBody($body);

        $handler = function ($request) use (&$parsedBody) {
            $parsedBody = $request->getParsedBody();

            return new Response();
        };
        $this->middleware->process($request, new HandlerProxy2($handler));

        $this->assertNull($parsedBody);
    }

    public function testGetParsedBodyWithJsonStructuredSuffix()
    {
        $request = $this->request->withHeader('Content-Type', 'application/vnd.api+json;charset=utf8');
        $body = StreamFactory::createFromStringOrResource('php://temp', 'rw+');
        $body->write('{"foo":"bar"}');
        $request = $request->withBody($body);

        $handler = function ($request) use (&$parsedBody) {
            $parsedBody = $request->getParsedBody();

            return new Response();
        };
        $this->middleware->process($request, new HandlerProxy2($handler));

        $this->assertEquals(['foo' => 'bar'], $parsedBody);
    }

    public function testGetParsedBodyXml()
    {
        $request = $this->request->withHeader('Content-Type', 'application/xml ;charset=utf8');
        $body = StreamFactory::createFromStringOrResource('php://temp', 'rw+');
        $body->write('<person><name>Josh</name></person>');
        $request = $request->withBody($body);

        $handler = function ($request) use (&$parsedBody) {
            $parsedBody = $request->getParsedBody();

            return new Response();
        };
        $this->middleware->process($request, new HandlerProxy2($handler));

        $this->assertEquals('Josh', $parsedBody->name);
    }

    public function testGetParsedBodyWithXmlStructuredSuffix()
    {
        $request = $this->request->withHeader('Content-Type', 'application/hal+xml;charset=utf8');
        $body = StreamFactory::createFromStringOrResource('php://temp', 'rw+');
        $body->write('<person><name>Josh</name></person>');
        $request = $request->withBody($body);

        $handler = function ($request) use (&$parsedBody) {
            $parsedBody = $request->getParsedBody();

            return new Response();
        };
        $this->middleware->process($request, new HandlerProxy2($handler));

        $this->assertEquals('Josh', $parsedBody->name);
    }

    public function testGetParsedBodyXmlWithTextXMLMediaType()
    {
        $request = $this->request->withHeader('Content-Type', 'text/xml');
        $body = StreamFactory::createFromStringOrResource('php://temp', 'rw+');
        $body->write('<person><name>Josh</name></person>');
        $request = $request->withBody($body);

        $handler = function ($request) use (&$parsedBody) {
            $parsedBody = $request->getParsedBody();

            return new Response();
        };
        $this->middleware->process($request, new HandlerProxy2($handler));

        $this->assertEquals('Josh', $parsedBody->name);
    }

    /**
     * Will fail if a simple_xml warning is created.
     */
    public function testInvalidXmlIsQuietForTextXml()
    {
        $request = $this->request->withHeader('Content-Type', 'text/xml');
        $body = StreamFactory::createFromStringOrResource('php://temp', 'rw+');
        $body->write('<person><name>Josh</name></invalid]>');
        $request = $request->withBody($body);

        $handler = function ($request) use (&$parsedBody) {
            $parsedBody = $request->getParsedBody();

            return new Response();
        };
        $this->middleware->process($request, new HandlerProxy2($handler));

        $this->assertEquals(null, $parsedBody);
    }

    /**
     * Will fail if a simple_xml warning is created.
     */
    public function testInvalidXmlIsQuietForApplicationXml()
    {
        $request = $this->request->withHeader('Content-Type', 'application/xml');
        $body = StreamFactory::createFromStringOrResource('php://temp', 'rw+');
        $body->write('<person><name>Josh</name></invalid]>');
        $request = $request->withBody($body);

        $handler = function ($request) use (&$parsedBody) {
            $parsedBody = $request->getParsedBody();

            return new Response();
        };
        $this->middleware->process($request, new HandlerProxy2($handler));

        $this->assertEquals(null, $parsedBody);
    }

    /*
    public function testGetParsedBodyWhenAlreadyParsed()
    {
        $request = $this->requestFactory();
        $prop = new ReflectionProperty($request, 'bodyParsed');
        $prop->setAccessible(true);
        $prop->setValue($request, ['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $request->getParsedBody());
    }*/
    /*
    public function testGetParsedBodyWhenBodyDoesNotExist()
    {
        $request = $this->requestFactory();
        $prop = new ReflectionProperty($request, 'body');
        $prop->setAccessible(true);
        $prop->setValue($request, null);
        $this->assertNull($request->getParsedBody());
    }*/
    /*
    public function testGetParsedBodyAfterCallReparseBody()
    {
        $uri = Uri::createFromString('https://example.com:443/?one=1');
        $headers = new Headers([
            'Content-Type' => 'application/x-www-form-urlencoded;charset=utf8',
        ]);
        $cookies = [];
        $serverParams = [];
        $body = new RequestBody();
        $body->write('foo=bar');
        $body->rewind();
        $request = new Request('POST', $uri, $headers, $cookies, $serverParams, $body);
        $this->assertEquals(['foo' => 'bar'], $request->getParsedBody());
        $newBody = new RequestBody();
        $newBody->write('abc=123');
        $newBody->rewind();
        $request = $request->withBody($newBody);
        $request->reparseBody();
        $this->assertEquals(['abc' => '123'], $request->getParsedBody());
    }*/
    /*
     * @expectedException \RuntimeException
     */
    /*
    public function testGetParsedBodyAsArray()
    {
        $uri = Uri::createFromString('https://example.com:443/foo/bar?abc=123');
        $headers = new Headers([
            'Content-Type' => 'application/json;charset=utf8',
        ]);
        $cookies = [];
        $serverParams = [];
        $body = new RequestBody();
        $body->write('{"foo": "bar"}');
        $body->rewind();
        $request = new Request('POST', $uri, $headers, $cookies, $serverParams, $body);
        $request->registerMediaTypeParser('application/json', function ($input) {
            return 10; // <-- Return invalid body value
        });
        $request->getParsedBody(); // <-- Triggers exception
    }*/
    /*
    public function testWithParsedBody()
    {
        $clone = $this->requestFactory()->withParsedBody(['xyz' => '123']);
        $this->assertEquals(['xyz' => '123'], $clone->getParsedBody());
    }*/
    /*
    public function testWithParsedBodyEmptyArray()
    {
        $method = 'GET';
        $uri = new Uri('https', 'example.com', 443, '/foo/bar', 'abc=123', '', '');
        $headers = new Headers();
        $headers->set('Content-Type', 'application/x-www-form-urlencoded;charset=utf8');
        $cookies = [];
        $serverParams = [];
        $body = new RequestBody();
        $body->write('foo=bar');
        $request = new Request($method, $uri, $headers, $cookies, $serverParams, $body);
        $clone = $request->withParsedBody([]);
        $this->assertEquals([], $clone->getParsedBody());
    }*/
    /*
    public function testWithParsedBodyNull()
    {
        $method = 'GET';
        $uri = new Uri('https', 'example.com', 443, '/foo/bar', 'abc=123', '', '');
        $headers = new Headers();
        $headers->set('Content-Type', 'application/x-www-form-urlencoded;charset=utf8');
        $cookies = [];
        $serverParams = [];
        $body = new RequestBody();
        $body->write('foo=bar');
        $request = new Request($method, $uri, $headers, $cookies, $serverParams, $body);
        $clone = $request->withParsedBody(null);
        $this->assertNull($clone->getParsedBody());
    }*/
    /*
    public function testGetParsedBodyReturnsNullWhenThereIsNoBodyData()
    {
        $request = $this->requestFactory(['REQUEST_METHOD' => 'POST']);
        $this->assertNull($request->getParsedBody());
    }*/
    /*
    public function testGetParsedBodyReturnsNullWhenThereIsNoMediaTypeParserRegistered()
    {
        $request = $this->requestFactory([
            'REQUEST_METHOD' => 'POST',
            'CONTENT_TYPE' => 'text/csv',
        ]);
        $request->getBody()->write('foo,bar,baz');
        $this->assertNull($request->getParsedBody());
    }*/
    /*
     * @expectedException \InvalidArgumentException
     */
    /*
    public function testWithParsedBodyInvalid()
    {
        $this->requestFactory()->withParsedBody(2);
    }*/
    /*
     * @expectedException \InvalidArgumentException
     */
    /*
    public function testWithParsedBodyInvalidFalseValue()
    {
        $this->requestFactory()->withParsedBody(false);
    }*/
}
