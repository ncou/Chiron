<?php

declare(strict_types=1);

namespace Chiron\Handler\Error;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class MaintenanceHandler implements ExceptionHandlerInterface
{
    public function handleException(Throwable $exception, ServerRequestInterface $request): ResponseInterface
    {
        $exception = $this->retrieveException($request);
        $response = $this->createResponseFromException($exception);

        if ($request->isAjax()) {
            $response = $response->write(json_encode([
                'status_code'   => 503,
                'reason_phrase' => 'Maintenance!',
            ]));
        } else {
            $response = $response->write(file_get_contents('503.html'));
        }

        return $response;
    }
}
