<?php

declare(strict_types=1);

namespace Chiron\Handler\Error;

use Throwable;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface for classes that handle exception.
 */
interface ExceptionHandlerInterface
{
    // TODO : add phpdoc bloc
    public function handleException(Throwable $exception, ServerRequestInterface $request): ResponseInterface;
}
