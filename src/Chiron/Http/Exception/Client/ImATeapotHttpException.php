<?php

declare(strict_types=1);

namespace Chiron\Http\Exception\Client;

use Chiron\Http\Exception\HttpException;

class ImATeapotHttpException extends HttpException
{
    public function __construct(string $message = 'I\'m a teapot', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(418, $message, $previous, $headers);
    }
}
