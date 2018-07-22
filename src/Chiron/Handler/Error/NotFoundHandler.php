<?php

declare(strict_types=1);

//https://github.com/slimphp/Slim/blob/3.x/Slim/Handlers/NotFound.php

namespace Chiron\Handler\Error;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class NotFoundHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        die('404 not found');
    }
}
