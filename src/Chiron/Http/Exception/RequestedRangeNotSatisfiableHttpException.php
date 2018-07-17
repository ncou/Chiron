<?php

declare(strict_types=1);

namespace Chiron\Http\Exception;

use Throwable;

class RequestedRangeNotSatisfiableHttpException extends HttpException
{
    public function __construct(string $message = 'Range Not Satisfiable', Throwable $previous = null, array $headers = [])
    {
        parent::__construct(416, $message, $previous, $headers);
    }
}
