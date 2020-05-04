<?php

declare(strict_types=1);

namespace Chiron\Facade;

final class HttpDecorator extends AbstractFacade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): string
    {
        return \Chiron\Pipe\HttpDecorator::class;
    }
}
