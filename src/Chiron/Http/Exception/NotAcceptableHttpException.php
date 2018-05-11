<?php

namespace Chiron\Http\Exception;

class NotAcceptableHttpException extends HttpException
{
    /**
     * @param string     $message  The internal exception message
     * @param \Exception $previous The previous exception
     * @param int        $code     The internal exception code
     * @param array      $headers
     */
    public function __construct(string $message = null, \Throwable $previous = null, array $headers = array())
    {
        parent::__construct(406, $message, $previous, $headers);
    }
}
