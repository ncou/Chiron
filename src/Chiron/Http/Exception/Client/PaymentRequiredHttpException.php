<?php

declare(strict_types=1);

namespace Chiron\Http\Exception\Client;

use Chiron\Http\Exception\HttpException;

class PaymentRequiredHttpException extends HttpException
{
    public function __construct(string $message = 'Payment Required', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(402, $message, $previous, $headers);
    }
}
