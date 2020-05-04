<?php

declare(strict_types=1);

namespace Chiron\Facade;

use Nyholm\Psr7Server\ServerRequestCreatorInterface;

final class Request extends AbstractFacade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): string
    {
        return ServerRequestCreatorInterface::class;
    }
}
