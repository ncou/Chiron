<?php

declare(strict_types=1);

namespace Chiron\Http\Response;

use Chiron\Http\Psr\Response;
use Chiron\Http\Stream\NullStream;

/**
 * A class representing empty HTTP responses.
 */
class EmptyResponse extends Response
{
    /**
     * Create an empty response with the given status code.
     *
     * @param int   $status  Status code for the response, if any.
     * @param array $headers Headers for the response, if any.
     */
    public function __construct($status = 204, array $headers = [])
    {
        //$body = new Stream('php://temp', 'r');
        $body = new NullStream();
        parent::__construct($status, $headers, $body);
    }

    /**
     * Create an empty response with the given headers.
     *
     * @param array $headers Headers for the response.
     *
     * @return EmptyResponse
     */
    // TODO : vérifier l'utilité de cette méthode !!!!
    public static function withHeaders(array $headers): self
    {
        return new static(204, $headers);
    }
}
