<?php

declare(strict_types=1);

namespace Chiron\Handler\Stack\Decorator;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FixedResponseHandler implements RequestHandlerInterface
{
    /**
     * fixed response to return.
     *
     * @var ResponseInterface
     */
    private $fixedResponse;

    /**
     * @param ResponseInterface $response always return this response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->fixedResponse = $response;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->fixedResponse;
    }
}
