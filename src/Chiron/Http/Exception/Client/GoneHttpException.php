<?php

declare(strict_types=1);

namespace Chiron\Http\Exception\Client;

use Chiron\Http\Exception\HttpException;

class GoneHttpException extends HttpException
{
    public function __construct(string $message = 'Gone', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(410, $message, $previous, $headers);
    }
}
