<?php

declare(strict_types=1);

namespace Chiron\Http\Exception\Client;

use Chiron\Http\Exception\HttpException;

class UnprocessableEntityHttpException extends HttpException
{
    public function __construct(string $message = 'Unprocessable Entity', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(422, $message, $previous, $headers);
    }
}
