<?php

declare(strict_types=1);

namespace Tests\Handler\Formatter;

use Chiron\Handler\Error\ExceptionHelper;
use Chiron\Http\Exception\HttpExceptionInterface;
use DOMDocument;
use DomXPath;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Chiron\Handler\Formatter\JsonFormatter;
use Chiron\Handler\ExceptionInfo;
use Chiron\Http\Exception\HttpException;
use Chiron\Http\Exception\Server\InternalServerErrorHttpException;
use Chiron\Http\Exception\Client\UnauthorizedHttpException;

class JsonFormatterTest extends TestCase
{
    public function testFormatServerError()
    {
        $formatter = new JsonFormatter();
        $formated = $formatter->format(new InternalServerErrorHttpException('Gutted!'));
        $expected = file_get_contents(__DIR__.'/Fixtures/500-json.txt');
        $this->assertSame(trim($expected), $formated);
    }

    public function testFormatClientError()
    {
        $formatter = new JsonFormatter();
        $formated = $formatter->format(new UnauthorizedHttpException('header'));



        $expected = file_get_contents(__DIR__.'/Fixtures/401-json.txt');
        $this->assertSame(trim($expected), $formated);
    }

    public function testPropertiesGetter()
    {
        $formatter = new JsonFormatter();
        $this->assertFalse($formatter->isVerbose());
        $this->assertTrue($formatter->canFormat(new InvalidArgumentException()));
        $this->assertSame('application/problem+json', $formatter->contentType());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Maximum stack depth exceeded
     */
    public function testThrowsOnEncodingExcededMaximumDepth()
    {
        $formatter = new JsonFormatter();

        $e = new InternalServerErrorHttpException();

        $array = array();
        for ($i=0; $i < 512; $i++) {
            $array = array($array);
        }

        $e->addAdditionalData('PHP_JSON_ERROR_DEPTH', $array);

        $formated = $formatter->format($e);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Malformed UTF-8 characters, possibly incorrectly encoded
     */
    public function testThrowsOnEncodingMalformedUTF8()
    {
        $formatter = new JsonFormatter();

        $e = new InternalServerErrorHttpException();
        $e->addAdditionalData('JSON_ERROR_UTF8', "\x80");

        $formated = $formatter->format($e);
    }



    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Recursion detected
     */
    public function testThrowsOnEncodingRecursion()
    {
        $formatter = new JsonFormatter();

        $e = new InternalServerErrorHttpException();

        $a = array();
        $a[] = &$a;

        $e->addAdditionalData('JSON_ERROR_RECURSION', $a);

        $formated = $formatter->format($e);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Inf and NaN cannot be JSON encoded
     */
    public function testThrowsOnEncodingINF()
    {
        $formatter = new JsonFormatter();

        $e = new InternalServerErrorHttpException();
        $e->addAdditionalData('JSON_ERROR_INF_OR_NAN', INF);

        $formated = $formatter->format($e);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Inf and NaN cannot be JSON encoded
     */
    public function testThrowsOnEncodingNAN()
    {
        $formatter = new JsonFormatter();

        $e = new InternalServerErrorHttpException();
        $e->addAdditionalData('JSON_ERROR_INF_OR_NAN', NAN);

        $formated = $formatter->format($e);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Type is not supported
     */
    public function testThrowsOnEncodingUnsupportedType()
    {
        $formatter = new JsonFormatter();

        $e = new InternalServerErrorHttpException();

        $resource = fopen(__FILE__, "r");

        $e->addAdditionalData('JSON_ERROR_UNSUPPORTED_TYPE', $resource);

        $formated = $formatter->format($e);
    }


}
