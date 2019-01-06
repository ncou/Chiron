<?php

declare(strict_types=1);

namespace Tests\Handler\Formatter;

use Chiron\Handler\Formatter\PlainTextFormatter;
use Chiron\Http\Exception\Client\UnauthorizedHttpException;
use Chiron\Http\Exception\Server\InternalServerErrorHttpException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PlainTextFormatterTest extends TestCase
{
    public function testFormatServerError()
    {
        $formatter = new PlainTextFormatter();
        $formated = $formatter->format(new InternalServerErrorHttpException('Gutted!'));
        $expected = file_get_contents(__DIR__ . '/Fixtures/500-plain.txt');
        $this->assertSame(trim($expected), $formated);
    }

    public function testFormatClientError()
    {
        $formatter = new PlainTextFormatter();
        $formated = $formatter->format(new UnauthorizedHttpException('header', 'Grrrr!'));
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
