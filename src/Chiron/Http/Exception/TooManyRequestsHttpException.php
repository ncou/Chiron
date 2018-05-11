<?php

namespace Chiron\Http\Exception;

class TooManyRequestsHttpException extends HttpException
{
    /**
     * @param int|string $retryAfter The number of seconds or HTTP-date after which the request may be retried
     * @param string     $message    The internal exception message
     * @param \Exception $previous   The previous exception
     * @param int        $code       The internal exception code
     * @param array      $headers
     */
    public function __construct($retryAfter = null, string $message = null, \Throwable $previous = null, array $headers = [])
    {
        if ($retryAfter) {
            $headers['Retry-After'] = $retryAfter;
        }

        parent::__construct(429, $message, $previous, $headers);
    }
}
