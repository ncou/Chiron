<?php

declare(strict_types=1);

namespace Chiron\Http\Exception\Server;

use Chiron\Http\Exception\HttpException;

class VariantAlsoNegotiatesHttpException extends HttpException
{
    public function __construct(string $message = 'Variant Also Negotiates', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(506, $message, $previous, $headers);
    }
}
