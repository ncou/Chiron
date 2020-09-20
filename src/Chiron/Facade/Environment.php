<?php

declare(strict_types=1);

namespace Chiron\Facade;

use Chiron\Core\Facade\AbstractFacade;

final class Environment extends AbstractFacade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): string
    {
        return \Chiron\Boot\Environment::class;
    }
}
