<?php

declare(strict_types=1);

namespace Tests\Handler\Formatter;

use Chiron\Handler\Formatter\ViewFormatter;
use Chiron\Http\Exception\Client\BadRequestHttpException;
use Chiron\Http\Exception\Server\InternalServerErrorHttpException;
use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;
use Chiron\Views\TemplateRendererInterface;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ViewFormatterTest extends TestCase
{
    public function testFormatServerError()
    {
        $exception = new InternalServerErrorHttpException();

        $viewRenderer = $this->createMock(TemplateRendererInterface::class);
        $viewRenderer->expects($this->once())->method('render')->with('errors/500', ['status' => 500, 'title' => 'Internal Server Error', 'detail' => 'An error has occurred and this resource cannot be displayed.', 'type' => 'https://httpstatuses.com/500', 'exception' => $exception])->willReturn('foobar');

        $request = new ServerRequest('GET', new Uri('/'));

        $formatter = new ViewFormatter($viewRenderer);
        $formatted = $formatter->format($request, $exception);
        $this->assertSame('foobar', $formatted);
    }

    public function testFormatClientError()
    {
        $exception = new BadRequestHttpException();

        $viewRenderer = $this->createMock(TemplateRendererInterface::class);
        $viewRenderer->expects($this->once())->method('render')->with('errors/400', ['status' => 400, 'title' => 'Bad Request', 'detail' => 'The request cannot be fulfilled due to bad syntax.', 'type' => 'https://httpstatuses.com/400', 'exception' => $exception])->willReturn('foobar');

        $request = new ServerRequest('GET', new Uri('/'));

        $formatter = new ViewFormatter($viewRenderer);
        $formatted = $formatter->format($request, $exception);
        $this->assertSame('foobar', $formatted);
    }

    public function testPhpError()
    {
        $exception = new Exception('test');
        $eConverted = new InternalServerErrorHttpException();

        $viewRenderer = $this->createMock(TemplateRendererInterface::class);
        $viewRenderer->expects($this->once())->method('render')->with('errors/500', ['status' => 500, 'title' => 'Internal Server Error', 'detail' => 'An error has occurred and this resource cannot be displayed.', 'type' => 'https://httpstatuses.com/500', 'exception' => $eConverted])->willReturn('foobar');

        $request = new ServerRequest('GET', new Uri('/'));

        $formatter = new ViewFormatter($viewRenderer);
        $formatted = $formatter->format($request, $exception);
        $this->assertSame('foobar', $formatted);
    }

    public function testPropertiesTrue_WithHttpException()
    {
        $viewRenderer = $this->createMock(TemplateRendererInterface::class);
        $viewRenderer->expects($this->once())->method('exists')->with('errors/500')->willReturn(true);

        $formatter = new ViewFormatter($viewRenderer);

        $this->assertFalse($formatter->isVerbose());
        $this->assertTrue($formatter->canFormat(new InternalServerErrorHttpException()));
        $this->assertSame('text/html', $formatter->contentType());
    }

    public function testPropertiesTrue_WithPhpException()
    {
        $viewRenderer = $this->createMock(TemplateRendererInterface::class);
        $viewRenderer->expects($this->once())->method('exists')->with('errors/500')->willReturn(true);

        $formatter = new ViewFormatter($viewRenderer);

        $this->assertFalse($formatter->isVerbose());
        $this->assertTrue($formatter->canFormat(new InvalidArgumentException()));
        $this->assertSame('text/html', $formatter->contentType());
    }

    public function testPropertiesFalse__WithHttpException()
    {
        $viewRenderer = $this->createMock(TemplateRendererInterface::class);
        $viewRenderer->expects($this->once())->method('exists')->with('errors/500')->willReturn(false);

        $formatter = new ViewFormatter($viewRenderer);

        $this->assertFalse($formatter->isVerbose());
        $this->assertFalse($formatter->canFormat(new InternalServerErrorHttpException()));
        $this->assertSame('text/html', $formatter->contentType());
    }

    public function testPropertiesFalse_WithPhpException()
    {
        $viewRenderer = $this->createMock(TemplateRendererInterface::class);
        $viewRenderer->expects($this->once())->method('exists')->with('errors/500')->willReturn(false);

        $formatter = new ViewFormatter($viewRenderer);

        $this->assertFalse($formatter->isVerbose());
        $this->assertFalse($formatter->canFormat(new InvalidArgumentException()));
        $this->assertSame('text/html', $formatter->contentType());
    }
}
