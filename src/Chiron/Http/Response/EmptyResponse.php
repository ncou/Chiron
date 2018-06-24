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
     * Create an empty response with the status code = 204.
     *
     * @param array $headers Headers for the response, if any.
     */
    public function __construct(array $headers = [])
    {
        //$body = new Stream('php://temp', 'r');
        $body = new NullStream();
        parent::__construct(204, $headers, $body);
    }
}
