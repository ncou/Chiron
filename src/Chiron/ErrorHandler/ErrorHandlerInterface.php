<?php

declare(strict_types=1);

namespace Chiron\ErrorHandler;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * Interface for classes that handle exception.
 */
interface ErrorHandlerInterface
{
    /**
     * Handle the exception and return PSR7 response.
     *
     * @param \Throwable                               $e
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param bool                                     $displayErrorDetails
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function renderException(Throwable $e, ServerRequestInterface $request, bool $displayErrorDetails): ResponseInterface;
}
