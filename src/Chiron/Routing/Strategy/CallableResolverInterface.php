<?php

declare(strict_types=1);

namespace Chiron\Routing\Strategy;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Chiron\Routing\Route;
use Chiron\Http\Psr\Response;
use Closure;

use Psr\Container\ContainerInterface;
use RuntimeException;
use InvalidArgumentException;

/**
 * Resolve a callable.
 */
interface CallableResolverInterface
{
    /**
     * Invoke the resolved callable.
     *
     * @param callable|string $toResolve
     *
     * @return callable
     */
    public function resolve($toResolve): callable;

}
