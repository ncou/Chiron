<?php

namespace Chiron\Container\Exception;

use Psr\Container\NotFoundExceptionInterface;

/**
 * No entry was found in the container.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class EntryNotFoundException extends \RuntimeException implements NotFoundExceptionInterface
{
    public function __construct($id)
    {
        parent::__construct(sprintf('Identifier "%s" is not defined in the container.', $id));
    }
}
