<?php

namespace Chiron\Http\Exception;

class ProxyAuthenticationRequiredHttpException extends HttpException
{
    /**
     * Constructor.
     *
     * @param string     $message
     * @param \Exception $previous
     * @param int        $code
     */
    public function __construct(string $message = 'Proxy Authentication Required', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(407, $message, $previous, $headers);
    }
}
