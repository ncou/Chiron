<?php

declare(strict_types=1);

namespace Tests\ErrorHandler\Formatter;

use Chiron\ErrorHandler\Formatter\XmlFormatter;
use Chiron\Http\Exception\Client\UnauthorizedHttpException;
use Chiron\Http\Exception\Server\InternalServerErrorHttpException;
use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class XmlFormatterTest extends TestCase
{
    public function testFormatServerError()
    {
        $request = new ServerRequest('GET', new Uri('/'));

        $formatter = new XmlFormatter();
        $formated = $formatter->format($request, new InternalServerErrorHttpException('Gutted!'));
        $expected = file_get_contents(__DIR__ . '/Fixtures/500-xml.txt');
        $this->assertSame(trim($expected), trim($formated));
    }

    public function testFormatClientError()
    {
        $request = new ServerRequest('GET', new Uri('/'));

        $formatter = new XmlFormatter();
        $formated = $formatter->format($request, new UnauthorizedHttpException('Grrrr!'));
        $expected = file_get_contents(__DIR__ . '/Fixtures/401-xml.txt');
        $this->assertSame(trim($expected), trim($formated));
    }

    public function testFormatPhpError()
    {
        $request = new ServerRequest('GET', new Uri('/'));

        $formatter = new XmlFormatter();
        $formated = $formatter->format($request, new Exception('This message will not be displayed!'));
        $expected = file_get_contents(__DIR__ . '/Fixtures/500-xml_v2.txt');
        $this->assertSame(trim($expected), trim($formated));
    }

    public function testPropertiesGetter()
    {
        $formatter = new XmlFormatter();
        $this->assertFalse($formatter->isVerbose());
        $this->assertTrue($formatter->canFormat(new InvalidArgumentException()));
        $this->assertSame('application/xml', $formatter->contentType());
    }

    public function testFormatServerErrorWithAdditionalData()
    {
        $request = new ServerRequest('GET', new Uri('/'));
        $e = new InternalServerErrorHttpException('Gutted!');

        $e->addAdditionalData('scalar', ['boolean' => true, 'empty' => '', 'null' => null, 'float' => 12.01, 'float_keep_zero' => 12.0, 'int' => 0, 'infinity' => INF, 'NaN' => NAN]);
        $e->addAdditionalData('array', ['foo', 'bar', ['baz']]);
        $e->addAdditionalData('class', new \stdClass());

        $formatter = new XmlFormatter();
        $formated = $formatter->format($request, $e);
        $expected = file_get_contents(__DIR__ . '/Fixtures/500-xml_v3.txt');

        $this->assertSame(trim($expected), trim($formated));
    }
}
