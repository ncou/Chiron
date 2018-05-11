<?php

namespace Chiron\Http\Exception;

class LockedHttpException extends HttpException
{
    /**
     * Constructor.
     *
     * @param string     $message
     * @param \Exception $previous
     * @param int        $code
     */
    public function __construct(string $message = 'Locked', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(423, $message, $previous, $headers);
    }
}
