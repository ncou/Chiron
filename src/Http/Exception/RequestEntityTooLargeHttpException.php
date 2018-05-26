<?php

declare(strict_types=1);

namespace Chiron\Http\Exception;

use Throwable;

class RequestEntityTooLargeHttpException extends HttpException
{
    public function __construct(string $message = 'Request Entity Too Large', Throwable $previous = null, array $headers = [])
    {
        parent::__construct(413, $message, $previous, $headers);
    }
}
