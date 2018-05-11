<?php

namespace Chiron\Http\Exception;

class GatewayTimeoutHttpException extends HttpException
{
    /**
     * Constructor
     *
     * @param string     $message
     * @param \Exception $previous
     * @param integer    $code
     */
    public function __construct(string $message = 'Gateway Timeout', \Throwable $previous = null, array $headers = array())
    {
        parent::__construct(504, $message, $previous, $headers);
    }
}
