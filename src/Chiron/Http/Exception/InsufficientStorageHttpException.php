<?php

declare(strict_types=1);

namespace Chiron\Http\Exception;

use Throwable;

class InsufficientStorageHttpException extends HttpException
{
    public function __construct(string $message = 'Insufficient Storage', Throwable $previous = null, array $headers = [])
    {
        parent::__construct(507, $message, $previous, $headers);
    }
}
