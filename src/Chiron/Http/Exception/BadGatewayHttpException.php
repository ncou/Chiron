<?php

declare(strict_types=1);

namespace Chiron\Http\Exception;

use Throwable;

class BadGatewayHttpException extends HttpException
{
    public function __construct(string $message = 'Bad Gateway', Throwable $previous = null, array $headers = [])
    {
        parent::__construct(502, $message, $previous, $headers);
    }
}
