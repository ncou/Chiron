<?php

namespace Chiron\Http\Exception;

class RequestedRangeNotSatisfiableHttpException extends HttpException
{
    /**
     * Constructor
     *
     * @param string     $message
     * @param \Exception $previous
     * @param integer    $code
     */
    public function __construct(string $message = 'Requested Range Not Satisfiable', \Throwable $previous = null, array $headers = array())
    {
        parent::__construct(416, $message, $previous, $headers);
    }
}
