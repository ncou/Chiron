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

class JsonFormatterTest extends TestCase
{
    private $info;

    protected function setUp()
    {
        $this->info = new ExceptionInfo(__DIR__.'/../../../resources/lang/en/errors.json');
    }

    public function testFormatServerError()
    {
        $formatter = new JsonFormatter($this->info);
        $formated = $formatter->format(new HttpException(500, 'Gutted!'));
        $expected = file_get_contents(__DIR__.'/Fixtures/500-json.txt');
        $this->assertSame(trim($expected), $formated);
    }

    public function testFormatClientError()
    {
        $formatter = new JsonFormatter($this->info);
        $formated = $formatter->format(new HttpException(401, 'Grrrr!'));
        $expected = file_get_contents(__DIR__.'/Fixtures/401-json.txt');
        $this->assertSame(trim($expected), $formated);
    }

    public function testPropertiesGetter()
    {
        $formatter = new JsonFormatter($this->info);
        $this->assertFalse($formatter->isVerbose());
        $this->assertTrue($formatter->canFormat(new InvalidArgumentException()));
        $this->assertSame('application/json', $formatter->contentType());
    }
}
