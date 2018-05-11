<?php

namespace Chiron\Http\Exception;

class PaymentRequiredHttpException extends HttpException
{
    /**
     * Constructor.
     *
     * @param string     $message
     * @param \Exception $previous
     * @param int        $code
     */
    public function __construct(string $message = 'Payment Required', \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(402, $message, $previous, $headers);
    }
}