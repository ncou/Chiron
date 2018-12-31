<?php

declare(strict_types=1);

namespace Chiron\Handler\Formatter;

use Chiron\Handler\ExceptionInfo;
use Chiron\Http\Exception\HttpException;
use Throwable;

class HtmlFormatter implements FormatterInterface
{
    /**
     * The html template file path.
     *
     * @var string
     */
    protected $path;

    /**
     * Create a new html displayer instance.
     *
     * @param string                          $path
     */
    public function __construct( string $path)
    {
        $this->path = $path;
    }

    public function format(Throwable $e): string
    {
        // This class doesn't show debug information, so by default we hide the php exception behind a neutral http 500 error.
        if (! $e instanceof HttpException) {
            $e = new \Chiron\Http\Exception\Server\InternalServerErrorHttpException();
        }

        return $this->render($e->toArray());
    }

    /**
     * Render the page with given data.
     *
     * @param array $data
     *
     * @return string
     */
    private function render(array $data)
    {
        $content = file_get_contents($this->path);

        foreach ($data as $key => $val) {
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
