<?php

declare(strict_types=1);

namespace Chiron\Handler\Error;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * Interface for classes that handle exception.
 */
interface ExceptionHandlerInterface
{
    // TODO : add phpdoc bloc
    public function handleException(Throwable $exception, ServerRequestInterface $request): ResponseInterface;
}
