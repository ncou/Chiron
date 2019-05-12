<?php

declare(strict_types=1);

namespace Chiron\Routing\Strategy;

use Chiron\Routing\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface StrategyInterface
{
    /**
     * Invoke the route callable based on the strategy.
     *
     * @param string|callable                          $handler
     * @param array                                    $params
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function invokeRouteHandler($handler, array $params, ServerRequestInterface $request): ResponseInterface;
}
