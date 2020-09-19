<?php

declare(strict_types=1);

namespace Chiron\Facade;

final class RequestContext extends AbstractFacade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): string
    {
        return Chiron\Http\RequestContext::class;
    }
}
