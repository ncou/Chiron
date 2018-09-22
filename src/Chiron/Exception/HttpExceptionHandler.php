<?php

declare(strict_types=1);

namespace Chiron\Exception;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class HttpExceptionHandler extends ExceptionHandler
{
    public function render(Throwable $e, ServerRequestInterface $request): ResponseInterface
    {
        $response = parent::render($e, $request);

        $headers = $e->getHeaders();
        foreach ($headers as $header => $value) {
            $response = $response->withHeader($header, $value);
        }

        return $response->withStatus($e->getStatusCode());
    }
}
