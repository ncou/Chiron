<?php

namespace Chiron\Http\Exception;

class UnavailableForLegalReasonsHttpException extends HttpException
{
    /**
     * Constructor
     *
     * @param string     $message
     * @param \Exception $previous
     * @param integer    $code
     */
    public function __construct(string $message = 'Unavailable For Legal Reasons', \Throwable $previous = null, array $headers = array())
    {
        parent::__construct(451, $message, $previous, $headers);
    }
}
