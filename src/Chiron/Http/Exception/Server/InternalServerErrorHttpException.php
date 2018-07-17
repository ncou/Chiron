<?php

declare(strict_types=1);

namespace Chiron\Http\Exception\Server;

use Chiron\Http\Exception\HttpException;

class InternalServerErrorHttpException extends HttpException
{
    public function __construct(string $message = 'Internal Server Error', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(500, $message, $previous, $headers);
    }
}
