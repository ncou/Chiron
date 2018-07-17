<?php

declare(strict_types=1);

namespace Chiron\Http\Exception\Client;

use Chiron\Http\Exception\HttpException;

class RequestTimeoutHttpException extends HttpException
{
    public function __construct(string $message = 'Request Timeout', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(408, $message, $previous, $headers);
    }
}
