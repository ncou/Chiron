<?php

declare(strict_types=1);

namespace Chiron\Handler\Error\Formatter;

use Chiron\Http\Exception\HttpExceptionInterface;
use Chiron\Handler\Error\ExceptionInfo;
use ErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use UnexpectedValueException;

use InvalidArgumentException;
use RuntimeException;

class TemplateHtmlFormatter implements ExceptionFormatterInterface
{
    /**
     * The exception info instance.
     *
     * @var \Chiron\Handler\Error\ExceptionInfo
     */
    protected $info;

    /**
     * The html template file path.
     *
     * @var string
     */
    protected $path;

    /**
     * Create a new html displayer instance.
     *
     * @param \Chiron\Handler\Error\ExceptionInfo $info
     * @param string                                            $path
     *
     * @return void
     */
    public function __construct(ExceptionInfo $info, string $path)
    {
        $this->info = $info;
        $this->path = $path;
    }

    public function format(Throwable $e): string
    {
        $code = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
        $info = $this->info->generate($e, $code);
        return $this->render($info);
    }

    /**
     * Render the page with given info.
     *
     * @param array $info
     *
     * @return string
     */
    private function render(array $info)
    {
        $content = file_get_contents($this->path);
        //$generator = $this->assets;
        //$info['home_url'] = $generator('/');
        //$info['favicon_url'] = $generator('favicon.ico');
        foreach ($info as $key => $val) {
            $content = str_replace("{{ $$key }}", $val, $content);
        }
        return $content;
    }

    /**
     * Get the supported content type.
     *
     * @return string
     */
    public function contentType(): string
    {
        return 'text/html';
    }

    /**
     * Do we provide verbose information about the exception?
     *
     * @return bool
     */
    public function isVerbose(): bool
    {
        return false;
    }

    /**
     * Can we format the exception?
     *
     * @param \Throwable $e
     *
     * @return bool
     */
    public function canFormat(Throwable $e): bool
    {
        return true;
    }
}
