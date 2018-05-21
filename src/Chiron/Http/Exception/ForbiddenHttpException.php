<?php

declare(strict_types=1);

namespace Chiron\Http\Exception;
use Throwable;

class ForbiddenHttpException extends HttpException
{
    public function __construct(string $message = 'Forbidden', Throwable $previous = null, array $headers = [])
    {
        parent::__construct(403, $message, $previous, $headers);
    }
}
