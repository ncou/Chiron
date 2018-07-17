<?php

declare(strict_types=1);

namespace Chiron\Http\Exception\Client;

use Chiron\Http\Exception\HttpException;

class RequestHeaderFieldsTooLargeHttpException extends HttpException
{
    public function __construct(string $message = 'Request Header Fields Too Large', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(431, $message, $previous, $headers);
    }
}
