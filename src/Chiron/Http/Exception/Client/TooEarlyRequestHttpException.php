<?php

declare(strict_types=1);

namespace Chiron\Http\Exception\Client;

use Chiron\Http\Exception\HttpException;

class TooEarlyRequestHttpException extends HttpException
{
    public function __construct(string $message = 'Too Early', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(425, $message, $previous, $headers);
    }
}
