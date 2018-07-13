<?php

declare(strict_types=1);

namespace Chiron\Http\Parser;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface defining a body parser.
 */
interface ParserInterface
{
    /**
     * Match the content type to the parser criteria.
     *
     * @return bool Whether or not the parser matches.
     */
    public function match(string $contentType) : bool;

    /**
     * Parse the body content and return a new request.
     *
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    public function parse(ServerRequestInterface $request) : ServerRequestInterface;
}
