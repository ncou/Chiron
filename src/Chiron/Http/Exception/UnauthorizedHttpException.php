<?php

declare(strict_types=1);

namespace Chiron\Http\Exception;

use Throwable;

class UnauthorizedHttpException extends HttpException
{
    public function __construct(string $challenge, string $message = 'Unauthorized', Throwable $previous = null, array $headers = [])
    {
        $headers['WWW-Authenticate'] = $challenge;

        parent::__construct(401, $message, $previous, $headers);
    }
}
