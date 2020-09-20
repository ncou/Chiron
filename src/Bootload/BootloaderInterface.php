<?php

declare(strict_types=1);

namespace Chiron\Bootload;

use Psr\Container\ContainerInterface;

interface BootloaderInterface
{
    public function bootload(ContainerInterface $container): void;
}
