<?php

namespace Chiron\Http\Exception;

class RequestTimeoutHttpException extends HttpException
{
    /**
     * Constructor.
     *
     * @param string     $message
     * @param \Exception $previous
     * @param int        $code
     */
    public function __construct(string $message = 'Request Timeout', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(408, $message, $previous, $headers);
    }
}
