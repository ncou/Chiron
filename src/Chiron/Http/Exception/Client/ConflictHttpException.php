<?php

declare(strict_types=1);

namespace Chiron\Http\Exception\Client;

use Chiron\Http\Exception\HttpException;

class ConflictHttpException extends HttpException
{
    public function __construct(string $message = 'Conflict', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(409, $message, $previous, $headers);
    }
}
