<?php

declare(strict_types=1);

namespace Chiron\Http\Exception\Client;

use Chiron\Http\Exception\HttpException;

class MisdirectedRequestHttpException extends HttpException
{
    public function __construct(string $message = 'Misdirected Request', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(421, $message, $previous, $headers);
    }
}
