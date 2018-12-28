<?php

declare(strict_types=1);

namespace Tests\Handler\Formatter;

use Chiron\Handler\Error\ExceptionHelper;
use Chiron\Http\Exception\HttpExceptionInterface;
use DOMDocument;
use DomXPath;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Chiron\Handler\Formatter\WhoopsFormatter;
use Chiron\Handler\ExceptionInfo;
use Chiron\Http\Exception\HttpException;

class WhoopsFormatterTest extends TestCase
{
    public function testFormatServerError()
    {
        $displayer = new WhoopsFormatter();
        $formatted = $displayer->format(new HttpException(502, 'Oh noes!'));
        $this->assertInternalType('string', $formatted);
    }
    public function testFormatClientError()
    {
        $displayer = new WhoopsFormatter();
        $formatted = $displayer->format(new HttpException(404, 'Arghhhh!'));
        $this->assertInternalType('string', $formatted);
    }
    public function testPropertiesGetter()
    {
        $displayer = new WhoopsFormatter();
        $this->assertTrue($displayer->isVerbose());
        $this->assertTrue($displayer->canFormat(new InvalidArgumentException()));
        $this->assertSame('text/html', $displayer->contentType());
    }
}
