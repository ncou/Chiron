<?php
/**
 * @see       https://github.com/zendframework/zend-stratigility for the canonical source repository
 *
 * @copyright Copyright (c) 2016-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-stratigility/blob/master/LICENSE.md New BSD License
 */
declare(strict_types=1);

namespace Tests\Http;

use Chiron\Http\Psr\Response;
use PHPUnit\Framework\TestCase;
/*
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
*/

use Psr\Http\Message\StreamInterface;

class ResponseTest extends TestCase
{
    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }

    public function createResponse(int $status = 200, array $headers = [], $body = null, string $version = '1.1', $reason = null)
    {
        return new Response($status, $headers, $body, $version, $reason);
    }

    public function testCreateEmptyResponseOK()
    {
        $r = $this->createResponse();

        $this->assertSame(200, $r->getStatusCode());
        $this->assertSame('1.1', $r->getProtocolVersion());
        $this->assertSame('OK', $r->getReasonPhrase());
        $this->assertSame([], $r->getHeaders());
        $this->assertInstanceOf(StreamInterface::class, $r->getBody());
        $this->assertSame('', (string) $r->getBody());
    }

    public function testAcceptRanges()
    {
        $r = $this->createResponse(200, ['Accept-Ranges' => 'bytes']);
        $this->assertSame('bytes', $r->getAcceptRanges());
    }

    public function testAge()
    {
        $r = $this->createResponse(200, ['Age' => '24']);
        $this->assertSame(24, $r->getAge());
    }

    public function testAllow()
    {
        $r = $this->createResponse(200, ['Allow' => 'GET, POST, HEAD']);
        $this->assertSame(['GET', 'POST', 'HEAD'], $r->getAllow());
    }

    public function testIsMethodAllowed()
    {
        $r = $this->createResponse(200, ['Allow' => 'GET, POST, HEAD']);
        $this->assertTrue($r->isMethodAllowed('GET'));
        $this->assertTrue($r->isMethodAllowed('gEt'));
        $this->assertFalse($r->isMethodAllowed('PUT'));
        $this->assertFalse($r->isMethodAllowed('PuT'));

        $r = $r->withoutHeader('Allow');
        $this->assertFalse($r->isMethodAllowed('GET'));
    }

    public function testIsOk()
    {
        $r = $this->createResponse(200);
        $this->assertTrue($r->isOk());
    }

    public function testIsEmpty()
    {
        $r = $this->createResponse(204);
        $this->assertTrue($r->isEmpty());

        $r = $r->withStatus(304);
        $this->assertTrue($r->isEmpty());
    }

    public function testIsRedirect()
    {
        $r = $this->createResponse(301);
        $this->assertTrue($r->isRedirect());

        $r = $r->withStatus(302);
        $this->assertTrue($r->isRedirect());

        $r = $r->withStatus(303);
        $this->assertTrue($r->isRedirect());

        $r = $r->withStatus(307);
        $this->assertTrue($r->isRedirect());

        $r = $r->withStatus(308);
        $this->assertTrue($r->isRedirect());
    }

    public function testIsInvalid()
    {
        $r = $this->createResponse(999);
        $this->assertTrue($r->isInvalid());

        $r = $r->withStatus(200);
        $this->assertFalse($r->isInvalid());
    }

    public function testIsInformational()
    {
        $r = $this->createResponse(101);
        $this->assertTrue($r->isInformational());
    }

    public function testIsSuccessful()
    {
        $r = $this->createResponse(201);
        $this->assertTrue($r->isSuccessful());
    }

    public function testIsRedirection()
    {
        $r = $this->createResponse(399);
        $this->assertTrue($r->isRedirection());
    }

    public function testIsClientError()
    {
        $r = $this->createResponse(401);
        $this->assertTrue($r->isClientError());
    }

    public function testIsServerError()
    {
        $r = $this->createResponse(501);
        $this->assertTrue($r->isServerError());
    }

    public function testIsError()
    {
        $r = $this->createResponse(401);
        $this->assertTrue($r->isError());

        $r = $r->withStatus(501);
        $this->assertTrue($r->isError());

        $r = $r->withStatus(200);
        $this->assertFalse($r->isError());
    }

    public function testIsForbidden()
    {
        $r = $this->createResponse(403);
        $this->assertTrue($r->isForbidden());
    }

    public function testIsNotFound()
    {
        $r = $this->createResponse(404);
        $this->assertTrue($r->isNotFound());
    }

    public function testIsMethodNotAllowed()
    {
        $r = $this->createResponse(405);
        $this->assertTrue($r->isMethodNotAllowed());
    }

    public function testDetectFormatByContentHeader()
    {
        $r = $this->createResponse(200, ['Content-Type' => 'text/html; charset=utf-8']);

        // no detection
        $this->assertSame(null, $r->detectFormat());

        // detect JSON
        $r = $r->withHeader('Content-Type', 'application/json');
        $this->assertSame('JSON', $r->detectFormat());

        // detect XML
        $r = $r->withHeader('Content-Type', 'application/xml');
        $this->assertSame('XML', $r->detectFormat());

        // detect URLENCODED
        $r = $r->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $this->assertSame('URLENCODED', $r->detectFormat());
    }

    public function testDetectFormatByContentBody()
    {
        $r = $this->createResponse();

        // no detection
        $this->assertSame(null, $r->detectFormat());

        // detect JSON
        $r = $r->withoutBody()->write('{"test":"ok"}');
        $this->assertSame('JSON', $r->detectFormat());

        // detect XML
        $r = $r->withoutBody()->write('<test>ok</test>');
        $this->assertSame('XML', $r->detectFormat());

        // detect URLENCODED
        $r = $r->withoutBody()->write('data=test&value=ok');
        $this->assertSame('URLENCODED', $r->detectFormat());
    }

    /**
     * @covers Chiron\Http\Response::withRedirect
     */
    public function testWithRedirect()
    {
        $r = $this->createResponse(200);
        $clone = $r->withRedirect('/foo', 301);
        $cloneWithDefaultStatus = $r->withRedirect('/foo');
        $cloneWithStatusMethod = $r->withStatus(301)->withRedirect('/foo');

        $this->assertSame(200, $r->getStatusCode());
        $this->assertFalse($r->hasHeader('Location'));

        $this->assertSame(301, $clone->getStatusCode());
        $this->assertTrue($clone->hasHeader('Location'));
        $this->assertEquals('/foo', $clone->getHeaderLine('Location'));

        $this->assertSame(302, $cloneWithDefaultStatus->getStatusCode());
        $this->assertTrue($cloneWithDefaultStatus->hasHeader('Location'));
        $this->assertEquals('/foo', $cloneWithDefaultStatus->getHeaderLine('Location'));

        $this->assertSame(301, $cloneWithStatusMethod->getStatusCode());
        $this->assertTrue($cloneWithStatusMethod->hasHeader('Location'));
        $this->assertEquals('/foo', $cloneWithStatusMethod->getHeaderLine('Location'));
    }

    /*
        public function testWithJson()
        {
            $data = ['foo' => 'bar1&bar2'];
            $originalResponse = new Response();
            $response = $originalResponse->withJson($data, 201);
            $this->assertNotEquals($response->getStatusCode(), $originalResponse->getStatusCode());
            $this->assertEquals(201, $response->getStatusCode());
            $this->assertEquals('application/json;charset=utf-8', $response->getHeaderLine('Content-Type'));
            $body = $response->getBody();
            $body->rewind();
            $dataJson = $body->getContents(); //json_decode($body->getContents(), true);
            $originalBody = $originalResponse->getBody();
            $originalBody->rewind();
            $originalContents = $originalBody->getContents();
            // test the original body hasn't be replaced
            $this->assertNotEquals($dataJson, $originalContents);
            $this->assertEquals('{"foo":"bar1&bar2"}', $dataJson);
            $this->assertEquals($data['foo'], json_decode($dataJson, true)['foo']);
            // Test encoding option
            $response = $response->withJson($data, 200, JSON_HEX_AMP);
            $body = $response->getBody();
            $body->rewind();
            $dataJson = $body->getContents();
            $this->assertEquals('{"foo":"bar1\u0026bar2"}', $dataJson);
            $this->assertEquals($data['foo'], json_decode($dataJson, true)['foo']);
            $response = $response->withStatus(201)->withJson([]);
            $this->assertEquals($response->getStatusCode(), 201);
        }
        */
    /*
     * @expectedException \RuntimeException
     */
    /*
    public function testWithInvalidJsonThrowsException()
    {
        $data = ['foo' => 'bar'.chr(233)];
        $this->assertEquals('bar'.chr(233), $data['foo']);
        $response = new Response();
        $response->withJson($data, 200);
        // Safety net: this assertion should not occur, since the RuntimeException
        // must have been caught earlier by the test framework
        $this->assertFalse(true);
    }
    */
    /*
    public function testStatusIsSetTo302IfLocationIsSetWhenStatusis200()
    {
        $response = new Response();
        $response = $response->withHeader('Location', '/foo');
        $this->assertSame(302, $response->getStatusCode());
    }*/
    /*
    public function testStatusIsNotSetTo302IfLocationIsSetWhenStatusisNot200()
    {
        $response = new Response();
        $response = $response->withStatus(201)->withHeader('Location', '/foo');
        $this->assertSame(201, $response->getStatusCode());
    }*/
}
