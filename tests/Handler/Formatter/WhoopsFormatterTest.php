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
use Chiron\Http\Exception\Server\InternalServerErrorHttpException;
use Chiron\Http\Exception\Client\BadRequestHttpException;

class WhoopsFormatterTest extends TestCase
{
    public function testFormatServerError()
    {
        $formatter = new WhoopsFormatter();
        $formatted = $formatter->format(new InternalServerErrorHttpException());
        $this->assertInternalType('string', $formatted);
    }
    public function testFormatClientError()
    {
        $formatter = new WhoopsFormatter();
        $formatted = $formatter->format(new BadRequestHttpException());
        $this->assertInternalType('string', $formatted);
    }
    public function testPropertiesGetter()
    {
        $formatter = new WhoopsFormatter();
        $this->assertTrue($formatter->isVerbose());
        $this->assertTrue($formatter->canFormat(new InvalidArgumentException()));
        $this->assertSame('text/html', $formatter->contentType());
    }
}
