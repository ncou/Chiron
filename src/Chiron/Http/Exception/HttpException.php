<?php

declare(strict_types=1);

namespace Chiron\Http\Exception;

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
        if ($statusCode < 400 || $statusCode > 599) {
            throw new \InvalidArgumentException("Invalid status code '$statusCode'; must be an integer between 400 and 599, inclusive.");
        }

        $this->statusCode = $statusCode;
        $this->headers = $headers;

        parent::__construct($message ?: '', 0, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
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

    // TODO : ajouter une méthode hasHeader() ????
    // TODO ; ajouter une méthode getHeader() ????
    // TODO : creer une fonction getHeaderLine() ???
}
