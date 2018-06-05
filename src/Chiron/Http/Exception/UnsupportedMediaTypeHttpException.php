<?php

declare(strict_types=1);

namespace Chiron\Http\Exception;

use Throwable;

class UnsupportedMediaTypeHttpException extends HttpException
{
    public function __construct(string $message = 'Unsupported Media Type', Throwable $previous = null, array $headers = [])
    {
        parent::__construct(415, $message, $previous, $headers);
    }
}