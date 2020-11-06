<?php

declare(strict_types=1);

namespace Chiron\Core\Container\Bootloader;

use Psr\Container\ContainerInterface;

interface BootloaderInterface
{
    public function bootload(ContainerInterface $container): void;
}
