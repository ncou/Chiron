<?php

declare(strict_types=1);

namespace Chiron\Facade;

use Psr\Http\Message\ResponseFactoryInterface;

/**
 * @method static \Psr\Http\Message\ResponseInterface createResponse(int $code = 200, string $reasonPhrase = '')
 *
 * @see \Chiron\Http\Factory\ResponseFactory
 */
final class Response extends AbstractFacade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): string
    {
        return ResponseFactoryInterface::class;
    }
}
