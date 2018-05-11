<?php

namespace Chiron\Http\Exception;

class UnauthorizedHttpException extends HttpException
{
    /**
     * @param string     $challenge WWW-Authenticate challenge string
     * @param string     $message   The internal exception message
     * @param \Exception $previous  The previous exception
     * @param int        $code      The internal exception code
     * @param array      $headers
     */
    public function __construct(string $challenge, string $message = null, \Throwable $previous = null, array $headers = [])
    {
        $headers['WWW-Authenticate'] = $challenge;

        parent::__construct(401, $message, $previous, $headers);
    }
}
