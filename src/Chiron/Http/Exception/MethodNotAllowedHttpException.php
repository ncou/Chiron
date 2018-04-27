<?php

//namespace Slim\Exception;

namespace Chiron\Http\Exception;

class MethodNotAllowedHttpException extends HttpException
{
    /**
     * @param array      $allow    An array of allowed methods
     * @param string     $message  The internal exception message
     * @param \Exception $previous The previous exception
     * @param int        $code     The internal exception code
     * @param array      $headers
     */
    public function __construct(array $allow = [], string $message = 'Method Not Allowed', \Throwable $previous = null, array $headers = [])
    {
        if (! empty($allow)) {
            $headers['Allow'] = strtoupper(implode(', ', $allow));
        }

        parent::__construct(405, $message, $previous, $headers);
    }
}
