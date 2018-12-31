<?php

declare(strict_types=1);

namespace Tests\Handler\Formatter;

use Chiron\Handler\Error\ExceptionHelper;
use Chiron\Http\Exception\HttpExceptionInterface;
use DOMDocument;
use DomXPath;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Chiron\Handler\Formatter\XmlFormatter;
use Chiron\Handler\ExceptionInfo;
use Chiron\Http\Exception\HttpException;
use Chiron\Http\Exception\Server\InternalServerErrorHttpException;
use Chiron\Http\Exception\Client\UnauthorizedHttpException;

class XmlFormatterTest extends TestCase
{
    public function testFormatServerError()
    {
        $formatter = new XmlFormatter();
        $formated = $formatter->format(new InternalServerErrorHttpException('Gutted!'));
        $expected = file_get_contents(__DIR__.'/Fixtures/500-xml.txt');
        $this->assertSame(trim($expected), trim($formated));
    }

    public function testFormatClientError()
    {
        $formatter = new XmlFormatter();
        $formated = $formatter->format(new UnauthorizedHttpException('Grrrr!'));
        $expected = file_get_contents(__DIR__.'/Fixtures/401-xml.txt');
        $this->assertSame(trim($expected), trim($formated));
    }

    public function testPropertiesGetter()
    {
        $formatter = new XmlFormatter();
        $this->assertFalse($formatter->isVerbose());
        $this->assertTrue($formatter->canFormat(new InvalidArgumentException()));
        $this->assertSame('application/xml', $formatter->contentType());
    }
}
