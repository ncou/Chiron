<?php

namespace Chiron\Http\Exception;

class RequestUriTooLongHttpException extends HttpException
{
    /**
     * Constructor.
     *
     * @param string     $message
     * @param \Exception $previous
     * @param int        $code
     */
    public function __construct(string $message = 'Request-URI Too Long', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(414, $message, $previous, $headers);
    }
}
