<?php

namespace Chiron\Http\Exception;

class InternalServerErrorHttpException extends HttpException
{
    /**
     * Constructor.
     *
     * @param string     $message
     * @param \Exception $previous
     * @param int        $code
     */
    public function __construct(string $message = 'Internal Server Error', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(500, $message, $previous, $headers);
    }
}
