<?php

declare(strict_types=1);

namespace Chiron\Http\Exception\Client;

use Chiron\Http\Exception\HttpException;

class FailedDependencyHttpException extends HttpException
{
    public function __construct(string $message = 'Failed Dependency', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(424, $message, $previous, $headers);
    }
}
