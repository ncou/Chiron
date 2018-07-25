<?php

declare(strict_types=1);

//https://github.com/slimphp/Slim/blob/3.x/Slim/Handlers/NotFound.php

namespace Chiron\Handler\Error;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class NotFoundHandler implements ExceptionHandlerInterface
{
    public function handleException(Throwable $exception, ServerRequestInterface $request): ResponseInterface
    {
        die('404 not found');
    }
}
