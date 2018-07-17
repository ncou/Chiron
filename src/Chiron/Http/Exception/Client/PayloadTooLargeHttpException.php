<?php

declare(strict_types=1);

namespace Chiron\Http\Exception\Client;

use Chiron\Http\Exception\HttpException;

class PayloadTooLargeHttpException extends HttpException
{
    public function __construct(string $message = 'Payload Too Large', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(413, $message, $previous, $headers);
    }
}
