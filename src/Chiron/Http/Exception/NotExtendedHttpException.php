<?php

declare(strict_types=1);

namespace Chiron\Http\Exception;

use Throwable;

class NotExtendedHttpException extends HttpException
{
    public function __construct(string $message = 'Not Extended', Throwable $previous = null, array $headers = [])
    {
        parent::__construct(510, $message, $previous, $headers);
    }
}
