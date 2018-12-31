<?php

declare(strict_types=1);

namespace Tests\Handler\Formatter;

use Chiron\Handler\Error\ExceptionHelper;
use Chiron\Http\Exception\HttpExceptionInterface;
use DOMDocument;
use DomXPath;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Chiron\Handler\Formatter\PlainTextFormatter;
use Chiron\Handler\ExceptionInfo;
use Chiron\Http\Exception\HttpException;
use Chiron\Http\Exception\Server\InternalServerErrorHttpException;
use Chiron\Http\Exception\Client\UnauthorizedHttpException;

class PlainTextFormatterTest extends TestCase
{
    public function testFormatServerError()
    {
        $formatter = new PlainTextFormatter();
        $formated = $formatter->format(new InternalServerErrorHttpException('Gutted!'));
        $expected = file_get_contents(__DIR__.'/Fixtures/500-plain.txt');
        $this->assertSame(trim($expected), $formated);
    }

    public function testFormatClientError()
    {
        $formatter = new PlainTextFormatter();
        $formated = $formatter->format(new UnauthorizedHttpException('header', 'Grrrr!'));
        $expected = file_get_contents(__DIR__.'/Fixtures/401-plain.txt');
        $this->assertSame(trim($expected), $formated);
    }

    public function testPropertiesGetter()
    {
        $formatter = new PlainTextFormatter();
        $this->assertFalse($formatter->isVerbose());
        $this->assertTrue($formatter->canFormat(new InvalidArgumentException()));
        $this->assertSame('text/plain', $formatter->contentType());
    }
}
