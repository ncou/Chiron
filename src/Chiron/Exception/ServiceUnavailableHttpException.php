<?php

//namespace Symfony\Component\HttpKernel\Exception;

namespace Chiron\Exception;

/**
 * @author Ben Ramsey <ben@benramsey.com>
 */
class ServiceUnavailableHttpException extends HttpException
{
    /**
     * @param int|string $retryAfter The number of seconds or HTTP-date after which the request may be retried
     * @param string     $message    The internal exception message
     * @param \Exception $previous   The previous exception
     * @param int        $code       The internal exception code
     * @param array      $headers
     */
    public function __construct($retryAfter = null, string $message = null, \Throwable $previous = null, array $headers = array())
    {
        if ($retryAfter) {
            $headers['Retry-After'] = $retryAfter;
        }

        parent::__construct(503, $message, $previous, $headers);
    }
}
