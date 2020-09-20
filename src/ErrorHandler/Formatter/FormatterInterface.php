<?php

declare(strict_types=1);

namespace Chiron\ErrorHandler\Formatter;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * Interface for classes that parse the raw request body into a parameters array.
 */
interface FormatterInterface
{
    /**
     * Format the exception as a string.
     *
     * @param Psr\Http\Message\ServerRequestInterface $request
     * @param \Throwable                              $e
     *
     * @return string The formatted exception.
     */
    public function format(ServerRequestInterface $request, Throwable $e): string;

    /**
     * Get the supported content type.
     *
     * @return string
     */
    public function contentType(): string;

    /**
     * Do we provide verbose information about the exception?
     *
     * @return bool
     */
    public function isVerbose(): bool;

    /**
     * Can we format the exception?
     *
     * @param \Throwable $e
     *
     * @return bool
     */
    public function canFormat(Throwable $e): bool;
}
