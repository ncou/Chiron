<?php

declare(strict_types=1);

namespace Chiron\Facade;

use Chiron\Core\Facade\AbstractFacade;

/**
 * @method static \Psr\Http\Message\ResponseInterface createResponse(int $code = 200, string $reasonPhrase = '')
 *
 * @see \Chiron\Http\Factory\ResponseFactory
 */
// TODO : il faudrait plutot une facade pour la classe HttpFactory qui regroupe l'ensemble des factory psr17 !!!! Cette classe de facade ne sert pas à grand chose !!!
final class Response extends AbstractFacade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): string
    {
        // phpcs:ignore SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly.ReferenceViaFullyQualifiedName
        return \Psr\Http\Message\ResponseFactoryInterface::class;
    }
}
