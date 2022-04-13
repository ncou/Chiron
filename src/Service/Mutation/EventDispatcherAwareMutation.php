<?php

declare(strict_types=1);

namespace Chiron\Service\Mutation;

use Chiron\Config\InjectableConfigInterface;
use Chiron\Config\Configure;
use Chiron\Container\Container;
use Chiron\Event\EventDispatcherAwareInterface;
use Chiron\Event\EventDispatcherAwareTrait;
use Psr\EventDispatcher\EventDispatcherInterface;

final class EventDispatcherAwareMutation
{
    public static function mutation(EventDispatcherAwareInterface $eventized): void
    {
        // Inject the event dispatcher if not already present in the EventDispatcherAware instance object.
        if (! $eventized->hasEventDispatcher()) {
            $eventized->setEventDispatcher((Container::$instance)->get(EventDispatcherInterface::class)); // TODO : améliorer le code c'est pas super propre !!!!
        }
    }
}
