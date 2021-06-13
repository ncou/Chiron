<?php

declare(strict_types=1);

namespace Chiron\Service\Mutation;

use Chiron\Config\InjectableConfigInterface;
use Chiron\Container\Container;
use Chiron\Config\Configure;
use Chiron\Container\ContainerAwareInterface;
use Chiron\Container\ContainerAwareTrait;

final class ContainerAwareMutation
{
    public static function mutation(ContainerAwareInterface $containerized)
    {
        // Inject the container if not already present in the ContainerAware instance object.
        if (! $containerized->hasContainer()) {
            $containerized->setContainer(Container::$instance); // TODO : améliorer le code c'est pas super propre !!!!
        }
    }
}
