<?php

declare(strict_types=1);

namespace Chiron\Http\Exception\Client;

use Chiron\Http\Exception\HttpException;

class MethodNotAllowedHttpException extends HttpException
{
    public function __construct(array $allow = [], string $message = 'Method Not Allowed', \Throwable $previous = null, array $headers = [])
    {
        if (! empty($allow)) {
            $headers['Allow'] = strtoupper(implode(', ', $allow));
        }

        parent::__construct(405, $message, $previous, $headers);
    }
}
