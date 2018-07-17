<?php

declare(strict_types=1);

namespace Chiron\Http\Exception\Client;

use Chiron\Http\Exception\HttpException;

class TooManyRequestsHttpException extends HttpException
{
    public function __construct($retryAfter = null, string $message = 'Too Many Requests', \Throwable $previous = null, array $headers = [])
    {
        if ($retryAfter) {
            $headers['Retry-After'] = $retryAfter;
        }

        parent::__construct(429, $message, $previous, $headers);
    }
}
