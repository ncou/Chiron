<?php

namespace Chiron\Http\Exception;

class RequestEntityTooLargeHttpException extends HttpException
{
    /**
     * Constructor
     *
     * @param string     $message
     * @param \Exception $previous
     * @param integer    $code
     */
    public function __construct(string $message = 'Request Entity Too Large', \Throwable $previous = null, array $headers = array())
    {
        parent::__construct(413, $message, $previous, $headers);
    }
}
