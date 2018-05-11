<?php

namespace Chiron\Http\Exception;

/**
 * @author Ben Ramsey <ben@benramsey.com>
 */
class BadRequestHttpException extends HttpException
{
    /**
     * @param string     $message  The internal exception message
     * @param \Exception $previous The previous exception
     * @param int        $code     The internal exception code
     * @param array      $headers
     */
    public function __construct(string $message = null, \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(400, $message, $previous, $headers);
    }
}
