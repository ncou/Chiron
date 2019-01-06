<?php

declare(strict_types=1);

namespace Tests\Handler\Formatter;

use Chiron\Handler\Formatter\WhoopsFormatter;
use Chiron\Http\Exception\Client\BadRequestHttpException;
use Chiron\Http\Exception\Server\InternalServerErrorHttpException;
use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class WhoopsFormatterTest extends TestCase
{
    public function testFormatServerError()
    {
        $request = new ServerRequest('GET', new Uri('/'));

        $formatter = new WhoopsFormatter();
        $formatted = $formatter->format($request, new InternalServerErrorHttpException());
        $this->assertInternalType('string', $formatted);
    }

    public function testFormatClientError()
    {
        $request = new ServerRequest('GET', new Uri('/'));

        $formatter = new WhoopsFormatter();
        $formatted = $formatter->format($request, new BadRequestHttpException());
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
