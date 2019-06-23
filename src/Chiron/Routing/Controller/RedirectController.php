<?php

declare(strict_types=1);

namespace Chiron\Routing\Controller;

use Chiron\Kernel;
use Chiron\Routing\Route;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class RedirectController
{

    protected $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * Invoke the controller method.
     *
     * @param  string  $destination
     * @param  int  $status
     *
     * @return Psr\Http\Message\ResponseInterface
     */
    public function __invoke(string $destination, int $status): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($status);

        return $response->withHeader('Location', $destination);
    }
}
