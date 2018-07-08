<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Response;

use Chiron\Http\Response\TextResponse;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class TextResponseTest extends TestCase
{
    public function testConstructorAcceptsBodyAsString()
    {
        $body = 'Uh oh not found';
        $response = new TextResponse($body);
        $this->assertSame($body, (string) $response->getBody());
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testConstructorAllowsPassingStatus()
    {
        $body = 'Uh oh not found';
        $status = 404;
        $response = new TextResponse($body, $status);
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame($body, (string) $response->getBody());
    }

    public function testConstructorAllowsPassingHeaders()
    {
        $body = 'Uh oh not found';
        $status = 404;
        $headers = [
            'x-custom' => ['foo-bar'],
        ];
        $response = new TextResponse($body, $status, $headers);
        $this->assertSame(['foo-bar'], $response->getHeader('x-custom'));
        $this->assertSame('text/plain', $response->getHeaderLine('content-type'));
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame($body, (string) $response->getBody());
    }

    public function testConstructorWithContentTypeHeader()
    {
        $headers = [
            'Content-Type' => ['foo-bar'],
        ];
        $response = new TextResponse('', 200, $headers);
        $this->assertSame(['foo-bar'], $response->getHeader('Content-Type'));
    }

    public function testAllowsStreamsForResponseBody()
    {
        $stream = $this->prophesize(StreamInterface::class);
        $body = $stream->reveal();
        $response = new TextResponse($body);
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
        new TextResponse($body);
    }

    public function testConstructorRewindsBodyStream()
    {
        $text = 'test data';
        $response = new TextResponse($text);
        $actual = $response->getBody()->getContents();
        $this->assertSame($text, $actual);
    }
}
