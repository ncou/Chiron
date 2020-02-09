<?php

declare(strict_types=1);

namespace Chiron\Facade;

use Chiron\Router\Target\TargetFactory;

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
