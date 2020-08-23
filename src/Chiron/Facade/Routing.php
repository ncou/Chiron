<?php

declare(strict_types=1);

namespace Chiron\Facade;

use Chiron\Routing\RouteCollection;

// TODO : à déplacer dans le package "chiron/routing"
final class Routing extends AbstractFacade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): string
    {
        return RouteCollection::class;
    }
}
