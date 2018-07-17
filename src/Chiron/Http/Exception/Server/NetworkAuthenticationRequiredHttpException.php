<?php

declare(strict_types=1);

namespace Chiron\Http\Exception\Server;

use Chiron\Http\Exception\HttpException;

class NetworkAuthenticationRequiredHttpException extends HttpException
{
    public function __construct(string $message = 'Network Authentication Required', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(511, $message, $previous, $headers);
    }
}
