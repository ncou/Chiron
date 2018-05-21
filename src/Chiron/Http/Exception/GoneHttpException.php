<?php

declare(strict_types=1);

namespace Chiron\Http\Exception;
use Throwable;

class GoneHttpException extends HttpException
{
    public function __construct(string $message = 'Gone', Throwable $previous = null, array $headers = [])
    {
        parent::__construct(410, $message, $previous, $headers);
    }
}
