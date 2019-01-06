<?php

declare(strict_types=1);

namespace Chiron\Handler;

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
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Throwable                               $e
     * @param bool $displayErrorDetails
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request, Throwable $e, bool $displayErrorDetails): ResponseInterface;
}
