<?php

declare(strict_types=1);

namespace Tests\Handler\Formatter;

use Chiron\Handler\Formatter\JsonFormatter;
use Chiron\Http\Exception\Client\UnauthorizedHttpException;
use Chiron\Http\Exception\Server\InternalServerErrorHttpException;
use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class JsonFormatterTest extends TestCase
{
    public function testFormatServerError()
    {
        $request = new ServerRequest('GET', new Uri('/'));

        $formatter = new JsonFormatter();
        $formated = $formatter->format($request, new InternalServerErrorHttpException('Gutted!'));
        $expected = file_get_contents(__DIR__ . '/Fixtures/500-json.txt');
        $this->assertSame(trim($expected), $formated);
    }

    public function testFormatClientError()
    {
        $request = new ServerRequest('GET', new Uri('/'));

        $formatter = new JsonFormatter();
        $formated = $formatter->format($request, new UnauthorizedHttpException('header'));

        $expected = file_get_contents(__DIR__ . '/Fixtures/401-json.txt');
        $this->assertSame(trim($expected), $formated);
    }

    public function testFormatPhpError()
    {
        $request = new ServerRequest('GET', new Uri('/'));

        $formatter = new JsonFormatter();
        $formated = $formatter->format($request, new Exception('This message will not be displayed!'));

        $expected = file_get_contents(__DIR__ . '/Fixtures/500-json_v2.txt');
        $this->assertSame(trim($expected), $formated);
    }

    public function testPropertiesGetter()
    {
        $formatter = new JsonFormatter();
        $this->assertFalse($formatter->isVerbose());
        $this->assertTrue($formatter->canFormat(new InvalidArgumentException()));
        $this->assertSame('application/json', $formatter->contentType());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Maximum stack depth exceeded
     */
    public function testThrowsOnEncodingExcededMaximumDepth()
    {
        $request = new ServerRequest('GET', new Uri('/'));

        $formatter = new JsonFormatter();

        $e = new InternalServerErrorHttpException();

        $array = [];
        for ($i = 0; $i < 512; $i++) {
            $array = [$array];
        }

        $e->addAdditionalData('PHP_JSON_ERROR_DEPTH', $array);

        $formated = $formatter->format($request, $e);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Malformed UTF-8 characters, possibly incorrectly encoded
     */
    public function testThrowsOnEncodingMalformedUTF8()
    {
        $request = new ServerRequest('GET', new Uri('/'));

        $formatter = new JsonFormatter();

        $e = new InternalServerErrorHttpException();
        $e->addAdditionalData('JSON_ERROR_UTF8', "\x80");

        $formated = $formatter->format($request, $e);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Recursion detected
     */
    public function testThrowsOnEncodingRecursion()
    {
        $request = new ServerRequest('GET', new Uri('/'));

        $formatter = new JsonFormatter();

        $e = new InternalServerErrorHttpException();

        $a = [];
        $a[] = &$a;

        $e->addAdditionalData('JSON_ERROR_RECURSION', $a);

        $formated = $formatter->format($request, $e);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Inf and NaN cannot be JSON encoded
     */
    public function testThrowsOnEncodingINF()
    {
        $request = new ServerRequest('GET', new Uri('/'));

        $formatter = new JsonFormatter();

        $e = new InternalServerErrorHttpException();
        $e->addAdditionalData('JSON_ERROR_INF_OR_NAN', INF);

        $formated = $formatter->format($request, $e);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Inf and NaN cannot be JSON encoded
     */
    public function testThrowsOnEncodingNAN()
    {
        $request = new ServerRequest('GET', new Uri('/'));

        $formatter = new JsonFormatter();

        $e = new InternalServerErrorHttpException();
        $e->addAdditionalData('JSON_ERROR_INF_OR_NAN', NAN);

        $formated = $formatter->format($request, $e);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Type is not supported
     */
    public function testThrowsOnEncodingUnsupportedType()
    {
        $request = new ServerRequest('GET', new Uri('/'));

        $formatter = new JsonFormatter();

        $e = new InternalServerErrorHttpException();

        $resource = fopen(__FILE__, 'r');

        $e->addAdditionalData('JSON_ERROR_UNSUPPORTED_TYPE', $resource);

        $formated = $formatter->format($request, $e);
    }
}
