<?php

declare(strict_types=1);

namespace Chiron\Facade;

final class ResponseCreator extends AbstractFacade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): string
    {
        return \Chiron\Http\Helper\ResponseCreator::class;
    }
}
