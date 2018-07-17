<?php

declare(strict_types=1);

namespace Chiron\Http\Exception\Server;

use Chiron\Http\Exception\HttpException;

class LoopDetectedHttpException extends HttpException
{
    public function __construct(string $message = 'Loop Detected', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(508, $message, $previous, $headers);
    }
}
