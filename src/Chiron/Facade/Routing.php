<?php

declare(strict_types=1);

namespace Chiron\Facade;

use Chiron\Core\Facade\AbstractFacade;

// TODO : à déplacer dans le package "chiron/routing"
final class Routing extends AbstractFacade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): string
    {
        return \Chiron\Routing\RouteCollection::class;
    }
}
