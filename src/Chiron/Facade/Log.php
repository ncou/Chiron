<?php

declare(strict_types=1);

namespace Chiron\Facade;

final class Log extends AbstractFacade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): string
    {
        return \Psr\Log\LoggerInterface::class;
    }
}
