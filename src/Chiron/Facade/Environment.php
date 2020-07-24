<?php

declare(strict_types=1);

namespace Chiron\Facade;

use Chiron\Boot\Directories;

final class Directories extends AbstractFacade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): string
    {
        return Directories::class;
    }
}
