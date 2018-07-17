<?php

declare(strict_types=1);

namespace Chiron\Http\Exception\Client;

use Chiron\Http\Exception\HttpException;

/**
 * @author Ben Ramsey <ben@benramsey.com>
 */
class BadRequestHttpException extends HttpException
{
    public function __construct(string $message = 'Bad Request', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(400, $message, $previous, $headers);
    }
}
