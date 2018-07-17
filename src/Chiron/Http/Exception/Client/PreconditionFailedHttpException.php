<?php

declare(strict_types=1);

namespace Chiron\Http\Exception\Client;

use Chiron\Http\Exception\HttpException;

class PreconditionFailedHttpException extends HttpException
{
    public function __construct(string $message = 'Precondition Failed', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(412, $message, $previous, $headers);
    }
}
