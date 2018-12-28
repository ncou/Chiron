<?php

declare(strict_types=1);

namespace Tests\Handler\Formatter;

use Chiron\Handler\Error\ExceptionHelper;
use Chiron\Http\Exception\HttpExceptionInterface;
use DOMDocument;
use DomXPath;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Chiron\Handler\Formatter\HtmlFormatter;
use Chiron\Handler\ExceptionInfo;
use Chiron\Http\Exception\HttpException;

class HtmlFormatterTest extends TestCase
{
    public function testFormatServerError()
    {
        $formatter = $this->getHtmlFormatter();
        $formatted = $formatter->format(new HttpException(502, 'Oh noes!'));
        $expected = file_get_contents(__DIR__.'/Fixtures/502-html.txt');
        $this->assertSame($expected, $formatted);
    }
    public function testFormatClientError()
    {
        $formatter = $this->getHtmlFormatter();
        $formatted = $formatter->format(new HttpException(404, 'Arghhhh!'));
        $expected = file_get_contents(__DIR__.'/Fixtures/404-html.txt');
        $this->assertSame($expected, $formatted);
    }
    public function testPropertiesGetter()
    {
        $formatter = $this->getHtmlFormatter();
        $this->assertFalse($formatter->isVerbose());
        $this->assertTrue($formatter->canFormat(new InvalidArgumentException()));
        $this->assertSame('text/html', $formatter->contentType());
    }
    private function getHtmlFormatter()
    {
        $info = new ExceptionInfo(__DIR__.'/../../../resources/lang/en/errors.json');
        return new HtmlFormatter($info, __DIR__.'/../../../resources/error.html');
    }
}
