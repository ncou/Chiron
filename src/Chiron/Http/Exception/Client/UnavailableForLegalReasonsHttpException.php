<?php

declare(strict_types=1);

namespace Chiron\Http\Exception\Client;

use Chiron\Http\Exception\HttpException;

class UnavailableForLegalReasonsHttpException extends HttpException
{
    public function __construct(string $message = 'Unavailable For Legal Reasons', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(451, $message, $previous, $headers);
    }
}
