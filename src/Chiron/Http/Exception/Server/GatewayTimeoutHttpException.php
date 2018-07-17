<?php

declare(strict_types=1);

namespace Chiron\Http\Exception\Server;

use Chiron\Http\Exception\HttpException;

class GatewayTimeoutHttpException extends HttpException
{
    public function __construct(string $message = 'Gateway Timeout', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(504, $message, $previous, $headers);
    }
}
