<?php

declare(strict_types=1);

namespace Chiron\Exception\Formatter;

use Chiron\Exception\ExceptionInfo;
use Chiron\Http\Exception\HttpExceptionInterface;
use Throwable;

class JsonFormatter implements FormatterInterface
{
    /**
     * The exception info instance.
     *
     * @var \Chiron\Exception\ExceptionInfo
     */
    protected $info;

    /**
     * Create a new json displayer instance.
     *
     * @param \Chiron\Exception\ExceptionInfo $info
     */
    public function __construct(ExceptionInfo $info)
    {
        $this->info = $info;
    }

    /**
     * Render JSON error.
     *
     * @param \Throwable $e
     *
     * @return string
     */
    public function format(Throwable $e): string
    {
        $code = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
        $info = $this->info->generate($e, $code);

        $error = ['status' => $info['code'], 'title' => $info['name'], 'detail' => $info['detail']];

        return json_encode(['error' => [$error]], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get the supported content type.
     *
     * @return string
     */
    public function contentType(): string
    {
        return 'application/json';
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