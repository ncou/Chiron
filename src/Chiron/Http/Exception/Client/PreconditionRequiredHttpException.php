<?php

declare(strict_types=1);

namespace Chiron\Http\Exception\Client;

use Chiron\Http\Exception\HttpException;

class PreconditionRequiredHttpException extends HttpException
{
    public function __construct(string $message = 'Precondition Required', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(428, $message, $previous, $headers);
    }
}
