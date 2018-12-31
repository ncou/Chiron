<?php

declare(strict_types=1);

namespace Tests\Handler\Formatter;

use Chiron\Handler\Error\ExceptionHelper;
use Chiron\Http\Exception\HttpExceptionInterface;
use DOMDocument;
use DomXPath;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Chiron\Handler\Formatter\ViewFormatter;
use Chiron\Handler\ExceptionInfo;
use Chiron\Http\Exception\HttpException;
use Chiron\Views\TemplateRendererInterface;
use Chiron\Http\Exception\Server\InternalServerErrorHttpException;
use Chiron\Http\Exception\Client\BadRequestHttpException;

class ViewFormatterTest extends TestCase
{
    public function testError()
    {
        $exception = new BadRequestHttpException();

        $viewRenderer = $this->createMock(TemplateRendererInterface::class);
        $viewRenderer->expects($this->once())->method('render')->with('errors/400', ['status' => 400, 'title' => 'Bad Request', 'detail' => 'The request cannot be fulfilled due to bad syntax.', 'type' => 'https://httpstatuses.com/400', 'exception' => $exception])->willReturn("Gutted.\n");

        $formatter = new ViewFormatter($viewRenderer);
        $formatted = $formatter->format($exception);
        $this->assertSame("Gutted.\n", $formatted);
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
