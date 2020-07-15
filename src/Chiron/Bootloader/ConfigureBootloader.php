<?php

declare(strict_types=1);

namespace Chiron\Bootloader;

use Chiron\Boot\Directories;
use Chiron\Bootload\AbstractBootloader;
use Chiron\Console\Config\ConsoleConfig;
use Chiron\Console\Console;
use Chiron\Exception\ApplicationException;
use Chiron\Config\Config;
use Chiron\Boot\Configure;
use Chiron\Container\Container;
use Chiron\Config\InjectableConfigInterface;
use Chiron\Config\InjectableConfigMutation;

final class ConfigureBootloader extends AbstractBootloader
{

    public function __construct(Container $container)
    {
        $container->inflector(InjectableConfigInterface::class, [InjectableConfigMutation::class, 'mutation']);
    }

    public function boot(Configure $configure, Directories $directories): void
    {
        // init the default values with the framework configs files.
        //$configure->loadFromDirectory($directories->get('@framework/config'));
        // merge the user "app" configs files in the general settings.
        $configure->loadFromDirectory($directories->get('@config'));
    }
}
