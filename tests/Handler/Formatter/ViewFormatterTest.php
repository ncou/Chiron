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

class ViewFormatterTest extends TestCase
{
    private $info;

    protected function setUp()
    {
        $this->info = new ExceptionInfo(__DIR__.'/../../../resources/lang/en/errors.json');
    }

    public function testError()
    {
        $exception = new HttpException(502, 'Oh noes!');

        $viewRenderer = $this->createMock(TemplateRendererInterface::class);
        $viewRenderer->expects($this->once())->method('render')->with('errors/502', ['code' => 502, 'name' => 'Bad Gateway', 'detail' => 'The server was acting as a gateway or proxy and received an invalid response from the upstream server.', 'exception' => $exception])->willReturn("Gutted.\n");

        $formatter = new ViewFormatter($this->info, $viewRenderer);
        $formatted = $formatter->format($exception);
        $this->assertSame("Gutted.\n", $formatted);
    }
    public function testPropertiesTrue_WithHttpException()
    {
        $viewRenderer = $this->createMock(TemplateRendererInterface::class);
        $viewRenderer->expects($this->once())->method('exists')->with('errors/500')->willReturn(true);

        $formatter = new ViewFormatter($this->info, $viewRenderer);

        $this->assertFalse($formatter->isVerbose());
        $this->assertTrue($formatter->canFormat(new HttpException(500)));
        $this->assertSame('text/html', $formatter->contentType());
    }
    public function testPropertiesTrue_WithPhpException()
    {
        $viewRenderer = $this->createMock(TemplateRendererInterface::class);
        $viewRenderer->expects($this->once())->method('exists')->with('errors/500')->willReturn(true);

        $formatter = new ViewFormatter($this->info, $viewRenderer);

        $this->assertFalse($formatter->isVerbose());
        $this->assertTrue($formatter->canFormat(new InvalidArgumentException()));
        $this->assertSame('text/html', $formatter->contentType());
    }
    public function testPropertiesFalse__WithHttpException()
    {
        $viewRenderer = $this->createMock(TemplateRendererInterface::class);
        $viewRenderer->expects($this->once())->method('exists')->with('errors/500')->willReturn(false);

        $formatter = new ViewFormatter($this->info, $viewRenderer);

        $this->assertFalse($formatter->isVerbose());
        $this->assertFalse($formatter->canFormat(new HttpException(500)));
        $this->assertSame('text/html', $formatter->contentType());
    }
    public function testPropertiesFalse_WithPhpException()
    {
        $viewRenderer = $this->createMock(TemplateRendererInterface::class);
        $viewRenderer->expects($this->once())->method('exists')->with('errors/500')->willReturn(false);

        $formatter = new ViewFormatter($this->info, $viewRenderer);

        $this->assertFalse($formatter->isVerbose());
        $this->assertFalse($formatter->canFormat(new InvalidArgumentException()));
        $this->assertSame('text/html', $formatter->contentType());
    }

}
