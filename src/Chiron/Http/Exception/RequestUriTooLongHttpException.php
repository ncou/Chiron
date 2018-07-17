<?php

declare(strict_types=1);

namespace Chiron\Http\Exception;

use Throwable;

class RequestUriTooLongHttpException extends HttpException
{
    public function __construct(string $message = 'URI Too Long', Throwable $previous = null, array $headers = [])
    {
        parent::__construct(414, $message, $previous, $headers);
    }
}
