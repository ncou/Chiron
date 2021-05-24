<?php

declare(strict_types=1);

namespace Chiron\Service\Facade;

use Chiron\Core\Facade\AbstractFacade;

final class Environment extends AbstractFacade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): string
    {
        // phpcs:ignore SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly.ReferenceViaFullyQualifiedName
        return \Chiron\Core\Environment::class;
    }
}
