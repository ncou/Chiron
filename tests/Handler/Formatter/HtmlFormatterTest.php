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
        $displayer = $this->getHtmlDisplayer();
        $formatted = $displayer->format(new HttpException(502, 'Oh noes!'));
        $expected = file_get_contents(__DIR__.'/Fixtures/502-html.txt');
        $this->assertSame($expected, $formatted);
    }
    public function testFormatClientError()
    {
        $displayer = $this->getHtmlDisplayer();
        $formatted = $displayer->format(new HttpException(404, 'Arghhhh!'));
        $expected = file_get_contents(__DIR__.'/Fixtures/404-html.txt');
        $this->assertSame($expected, $formatted);
    }
    public function testPropertiesGetter()
    {
        $displayer = $this->getHtmlDisplayer();
        $this->assertFalse($displayer->isVerbose());
        $this->assertTrue($displayer->canFormat(new InvalidArgumentException()));
        $this->assertSame('text/html', $displayer->contentType());
    }
    private function getHtmlDisplayer()
    {
        $info = new ExceptionInfo(__DIR__.'/../../../resources/lang/en/errors.json');
        return new HtmlFormatter($info, __DIR__.'/../../../resources/error.html');
    }
}
