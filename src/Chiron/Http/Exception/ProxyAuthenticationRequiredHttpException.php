<?php

declare(strict_types=1);

namespace Chiron\Http\Exception;
use Throwable;

class ProxyAuthenticationRequiredHttpException extends HttpException
{
    public function __construct(string $message = 'Proxy Authentication Required', Throwable $previous = null, array $headers = [])
    {
        parent::__construct(407, $message, $previous, $headers);
    }
}
