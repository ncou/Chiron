<?php

namespace Chiron\Http\Exception;

class ExpectationFailedHttpException extends HttpException
{
    /**
     * Constructor
     *
     * @param string     $message
     * @param \Exception $previous
     * @param integer    $code
     */
    public function __construct(string $message = 'Expectation Failed', \Throwable $previous = null, array $headers = array())
    {
        parent::__construct(417, $message, $previous, $headers);
    }
}
