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
use Exception;

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

    public function testFormatPhpError()
    {
        $request = new ServerRequest('GET', new Uri('/'));

        $formatter = new PlainTextFormatter();
        $formated = $formatter->format($request, new Exception('This message will not be displayed!'));
        $expected = file_get_contents(__DIR__ . '/Fixtures/500-plain_v2.txt');
        $this->assertSame(trim($expected), $formated);
    }

    public function testPropertiesGetter()
    {
        $formatter = new PlainTextFormatter();
        $this->assertFalse($formatter->isVerbose());
        $this->assertTrue($formatter->canFormat(new InvalidArgumentException()));
        $this->assertSame('text/plain', $formatter->contentType());
    }

    public function testFormatServerErrorWithAdditionalData()
    {
        $request = new ServerRequest('GET', new Uri('/'));
        $e = new InternalServerErrorHttpException('Gutted!');

        $e->addAdditionalData('scalar', ['boolean' => true, 'null' => null, 'float' => 12.01, 'int' => 0, 'infinity' => INF]);
        $e->addAdditionalData('array', ['foo', 'bar']);
        $e->addAdditionalData('class', new \stdClass());

        $formatter = new PlainTextFormatter();
        $formated = $formatter->format($request, $e);
        $expected = file_get_contents(__DIR__ . '/Fixtures/500-plain_v3.txt');

        $this->assertSame(trim($expected), $formated);
    }
}
