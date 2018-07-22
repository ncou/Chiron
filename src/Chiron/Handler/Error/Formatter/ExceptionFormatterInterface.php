<?php

declare(strict_types=1);

namespace Chiron\Handler\Error\Formatter;

/**
 * Interface for classes that parse the raw request body into a parameters array.
 */
interface ExceptionFormatterInterface
{
    /**
     * Format the exception as a string
     *
     * @param \Throwable            $exception
     * @return string The formatted exception.
     */
    public function formatException(\Throwable $exception, bool $displayErrorDetails): string;
}
