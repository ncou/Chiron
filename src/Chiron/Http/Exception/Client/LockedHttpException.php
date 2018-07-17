<?php

declare(strict_types=1);

namespace Chiron\Http\Exception\Client;

use Chiron\Http\Exception\HttpException;

class LockedHttpException extends HttpException
{
    public function __construct(string $message = 'Locked', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(423, $message, $previous, $headers);
    }
}
