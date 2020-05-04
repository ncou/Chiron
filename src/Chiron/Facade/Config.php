<?php

declare(strict_types=1);

namespace Chiron\Facade;

use Chiron\Config\ConfigManager;

final class Config extends AbstractFacade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): string
    {
        return ConfigManager::class;
    }
}
