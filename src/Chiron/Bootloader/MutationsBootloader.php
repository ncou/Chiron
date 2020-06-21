<?php

declare(strict_types=1);

namespace Chiron\Bootloader;

use Chiron\Bootload\AbstractBootloader;
use Chiron\Container\Container;
use Chiron\Config\InjectableConfigInterface;
use Chiron\Config\InjectableConfigMutation;

final class MutationsBootloader extends AbstractBootloader
{
    public function boot(Container $container): void
    {
        $container->inflector(InjectableConfigInterface::class, [InjectableConfigMutation::class, 'mutation']);
    }
}
