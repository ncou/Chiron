<?php

namespace Chiron\Http\Exception;

class BadGatewayHttpException extends HttpException
{
    /**
     * Constructor.
     *
     * @param string     $message
     * @param \Exception $previous
     * @param int        $code
     */
    public function __construct(string $message = 'Bad Gateway', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(502, $message, $previous, $headers);
    }
}
