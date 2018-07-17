<?php

declare(strict_types=1);

namespace Chiron\Http\Exception\Client;

use Chiron\Http\Exception\HttpException;

class NotAcceptableHttpException extends HttpException
{
    public function __construct(string $message = 'Not Acceptable', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(406, $message, $previous, $headers);
    }
}
