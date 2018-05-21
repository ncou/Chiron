<?php

declare(strict_types=1);

namespace Chiron\Http\Exception;
use Throwable;

class ExpectationFailedHttpException extends HttpException
{
    public function __construct(string $message = 'Expectation Failed', Throwable $previous = null, array $headers = [])
    {
        parent::__construct(417, $message, $previous, $headers);
    }
}
