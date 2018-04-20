<?php

declare(strict_types=1);

namespace Chiron\Exception;

use RuntimeException;
use Throwable;

//https://github.com/symfony/http-kernel/blob/master/Exception/HttpException.php
//https://github.com/stratifyphp/http/blob/master/src/Exception/HttpException.php

// TODO : regarder ici comment faire : https://github.com/juliangut/slim-exception/blob/master/src/

// CREER des exceptions dédiées pour l'erreur 404 et 405 : https://github.com/stratifyphp/http/blob/master/src/Exception/HttpMethodNotAllowed.php   /   https://github.com/stratifyphp/http/blob/master/src/Exception/HttpNotFound.php

class HttpException extends RuntimeException //implements \ExceptionInterface
{
    protected $statusCode;
    protected $headers;

    public function __construct(int $statusCode, string $message = null, Throwable $previous = null, array $headers = [])
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;

        parent::__construct($message ?: '', 0, $previous);
    }

    public function getStatusCode() : int
    {
        return $this->statusCode;
    }

    public function getHeaders() : array
    {
        return $this->headers;
    }

    /**
     * Set response headers.
     *
     * @param array $headers Response headers
     */
    public function setHeaders(array $headers) // TODO : ajouter une méthode addHeader() ????
    {
        $this->headers = $headers;
    }
}
