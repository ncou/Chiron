<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Response;

use Chiron\Http\Response\XmlResponse;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use const PHP_EOL;

class XmlResponseTest extends TestCase
{
    protected $contentType = 'application/xml';

    protected function createResponse($body,int $status = 200,array $headers = [])
    {
        return new XmlResponse($body, $status, $headers);
    }

    public function testConstructorAcceptsBodyAsString()
    {
        $body = 'Super valid XML';
        $response = $this->createResponse($body);
        $this->assertSame($body, (string) $response->getBody());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($this->contentType, $response->getHeaderLine('Content-Type'));
    }

    public function testConstructorAllowsPassingStatus()
    {
        $body = 'More valid XML';
        $status = 404;
        $response = $this->createResponse($body, $status);
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame($body, (string) $response->getBody());
    }

    public function testConstructorAllowsPassingHeaders()
    {
        $body = '<nearly>Valid XML</nearly>';
        $status = 404;
        $headers = [
            'x-custom' => ['foo-bar'],
        ];
        $response = $this->createResponse($body, $status, $headers);
        $this->assertSame(['foo-bar'], $response->getHeader('x-custom'));
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame($body, (string) $response->getBody());
    }

    public function testConstructorWithContentTypeHeader()
    {
        $headers = [
            'Content-Type' => ['foo-bar'],
        ];
        $response = $this->createResponse('', 200, $headers);
        $this->assertSame(['foo-bar'], $response->getHeader('Content-Type'));
    }

    public function testAllowsStreamsForResponseBody()
    {
        $stream = $this->prophesize(StreamInterface::class);
        $body = $stream->reveal();
        $response = $this->createResponse($body);
        $this->assertSame($body, $response->getBody());
    }

    public function invalidContent()
    {
        return [
            'null'       => [null],
            'true'       => [true],
            'false'      => [false],
            'zero'       => [0],
            'int'        => [1],
            'zero-float' => [0.0],
            'float'      => [1.1],
            'array'      => [['php://temp']],
            'object'     => [(object) ['php://temp']],
        ];
    }

    /**
     * @dataProvider invalidContent
     */
    public function testRaisesExceptionforNonStringNonStreamBodyContent($body)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createResponse($body);
    }

    public function testConstructorRewindsBodyStream()
    {
        $body = '<?xml version="1.0"?>' . PHP_EOL . '<something>Valid XML</something>';
        $response = $this->createResponse($body);
        $actual = $response->getBody()->getContents();
        $this->assertSame($body, $actual);
    }
}
