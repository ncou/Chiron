<?php

declare(strict_types=1);

namespace Chiron\Facade;

use Chiron\Router\RouteCollector;

final class Routing extends AbstractFacade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): string
    {
        return RouteCollector::class;
    }
}
