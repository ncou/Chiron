<?php

declare(strict_types=1);

namespace Chiron\Handler\Error;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * Interface for classes that handle exception.
 */
interface ExceptionHandlerInterface
{
    /**
     * Render the exception (format exception body and return a PSR7 response).
     *
     * @param \Throwable                               $e
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface The response with a formatted exception.
     */
    public function render(Throwable $e, ServerRequestInterface $request): ResponseInterface;

    /**
     * Report or log an exception.
     *
     * @param \Throwable                               $e
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function report(Throwable $e, ServerRequestInterface $request): void;
}
