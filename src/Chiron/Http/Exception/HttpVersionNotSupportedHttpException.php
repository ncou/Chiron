<?php

declare(strict_types=1);

namespace Chiron\Http\Exception;

use Throwable;

class HttpVersionNotSupportedHttpException extends HttpException
{
    public function __construct(string $message = 'HTTP Version Not Supported', Throwable $previous = null, array $headers = [])
    {
        parent::__construct(505, $message, $previous, $headers);
    }
}
