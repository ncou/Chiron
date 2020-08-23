<?php

declare(strict_types=1);

namespace Chiron\Facade;

use Chiron\Routing\Target\TargetFactory;

// TODO : à déplacer dans le package "chiron/routing"
final class Target extends AbstractFacade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): string
    {
        return TargetFactory::class;
    }
}
