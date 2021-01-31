<?php

declare(strict_types=1);

namespace Chiron\Bootloader;

use Chiron\Core\Directories;
use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Core\Publisher;

// TODO : fusionner avec la classe PublishAppBootloader
final class PublishConsoleBootloader extends AbstractBootloader
{
    public function boot(Publisher $publisher, Directories $directories): void
    {
        // copy the configuration file template from the package "config" folder to the user "config" folder.
        $publisher->add(__DIR__ . '/../../config/console.php.dist', $directories->get('@config/console.php'));
    }
}
