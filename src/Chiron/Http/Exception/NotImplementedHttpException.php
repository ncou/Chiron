<?php

namespace Chiron\Http\Exception;

class NotImplementedHttpException extends HttpException
{
    /**
     * Constructor
     *
     * @param string     $message
     * @param \Exception $previous
     * @param integer    $code
     */
    public function __construct(string $message = 'Not Implemented', \Throwable $previous = null, array $headers = array())
    {
        parent::__construct(501, $message, $previous, $headers);
    }
}
