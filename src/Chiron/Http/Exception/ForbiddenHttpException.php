<?php

namespace Chiron\Http\Exception;

class ForbiddenHttpException extends HttpException
{
    /**
     * Constructor.
     *
     * @param string     $message
     * @param \Exception $previous
     * @param int        $code
     */
    public function __construct(string $message = 'Forbidden', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(403, $message, $previous, $headers);
    }
}
