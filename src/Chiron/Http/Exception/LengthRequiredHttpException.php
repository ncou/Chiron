<?php

declare(strict_types=1);

namespace Chiron\Http\Exception;

use Throwable;

class LengthRequiredHttpException extends HttpException
{
    public function __construct(string $message = 'Length Required', Throwable $previous = null, array $headers = [])
    {
        parent::__construct(411, $message, $previous, $headers);
    }
}
