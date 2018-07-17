<?php

declare(strict_types=1);

namespace Chiron\Http\Exception\Server;

use Chiron\Http\Exception\HttpException;

class ServiceUnavailableHttpException extends HttpException
{
    public function __construct($retryAfter = null, string $message = 'Service Unavailable', \Throwable $previous = null, array $headers = [])
    {
        if ($retryAfter) {
            $headers['Retry-After'] = $retryAfter;
        }

        parent::__construct(503, $message, $previous, $headers);
    }
}
