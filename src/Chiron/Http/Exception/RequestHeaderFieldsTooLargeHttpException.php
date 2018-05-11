<?php

namespace Chiron\Http\Exception;

class RequestHeaderFieldsTooLargeHttpException extends HttpException
{
    /**
     * Constructor.
     *
     * @param string     $message
     * @param \Exception $previous
     * @param int        $code
     */
    public function __construct(string $message = 'Request Header Fields Too Large', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(431, $message, $previous, $headers);
    }
}
