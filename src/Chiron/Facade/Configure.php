<?php

declare(strict_types=1);

namespace Chiron\Facade;

final class Configure extends AbstractFacade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): string
    {
        return \Chiron\Boot\Configure::class;
    }
}
