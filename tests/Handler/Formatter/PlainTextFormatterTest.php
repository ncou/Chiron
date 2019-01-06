<?php

declare(strict_types=1);

namespace Tests\Handler\Formatter;

use Chiron\Handler\Formatter\PlainTextFormatter;
use Chiron\Http\Exception\Client\UnauthorizedHttpException;
use Chiron\Http\Exception\Server\InternalServerErrorHttpException;
use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PlainTextFormatterTest extends TestCase
{
    public function testFormatServerError()
    {
        $request = new ServerRequest('GET', new Uri('/'));

        $formatter = new PlainTextFormatter();
        $formated = $formatter->format($request, new InternalServerErrorHttpException('Gutted!'));
        $expected = file_get_contents(__DIR__ . '/Fixtures/500-plain.txt');
        $this->assertSame(trim($expected), $formated);
    }

    public function testFormatClientError()
    {
        $request = new ServerRequest('GET', new Uri('/'));

        $formatter = new PlainTextFormatter();
        $formated = $formatter->format($request, new UnauthorizedHttpException('header', 'Grrrr!'));
        $expected = file_get_contents(__DIR__ . '/Fixtures/401-plain.txt');
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
