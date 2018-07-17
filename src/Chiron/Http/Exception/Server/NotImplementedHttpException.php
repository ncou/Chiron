<?php

declare(strict_types=1);

namespace Chiron\Http\Exception\Server;

use Chiron\Http\Exception\HttpException;

class NotImplementedHttpException extends HttpException
{
    public function __construct(string $message = 'Not Implemented', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(501, $message, $previous, $headers);
    }
}
