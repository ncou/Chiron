<?php

declare(strict_types=1);

namespace Tests\Handler\Formatter;

use Chiron\Handler\Formatter\HtmlFormatter;
use Chiron\Http\Exception\Client\UnauthorizedHttpException;
use Chiron\Http\Exception\Server\InternalServerErrorHttpException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;

class HtmlFormatterTest extends TestCase
{
    public function testFormatServerError()
    {
        $request = new ServerRequest('GET', new Uri('/'));

        $formatter = new HtmlFormatter(__DIR__ . '/../../../resources/error.html');
        $formatted = $formatter->format($request, new InternalServerErrorHttpException('Gutted!'));
        $expected = file_get_contents(__DIR__ . '/Fixtures/500-html.txt');
        $this->assertSame($expected, $formatted);
    }

    public function testFormatClientError()
    {
        $request = new ServerRequest('GET', new Uri('/'));

        $formatter = new HtmlFormatter(__DIR__ . '/../../../resources/error.html');
        $formatted = $formatter->format($request, new UnauthorizedHttpException('header'));
        $expected = file_get_contents(__DIR__ . '/Fixtures/401-html.txt');
        $this->assertSame($expected, $formatted);
    }

    public function testPropertiesGetter()
    {
        $formatter = new HtmlFormatter(__DIR__ . '/../../../resources/error.html');
        $this->assertFalse($formatter->isVerbose());
        $this->assertTrue($formatter->canFormat(new InvalidArgumentException()));
        $this->assertSame('text/html', $formatter->contentType());
    }
}
