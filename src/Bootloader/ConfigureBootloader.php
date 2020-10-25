<?php

declare(strict_types=1);

namespace Chiron\Bootloader;

use Chiron\Core\Configure;
use Chiron\Core\Directories;
use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Config\InjectableConfigInterface;
use Chiron\Config\InjectableConfigMutation;
use Chiron\Container\Container;

final class ConfigureBootloader extends AbstractBootloader
{
    public function __construct(Container $container)
    {
        $container->inflector(InjectableConfigInterface::class, [InjectableConfigMutation::class, 'mutation']);
    }

    // TODO : attention il faudrait gérer le cas ou le répertoire "config" n'existe pas, car sinon la méthode loadFromDirectory lévera une exception !!!
    public function boot(Configure $configure, Directories $directories): void
    {
        // add the user configs files in the general settings.
        $configure->loadFromDirectory($directories->get('@config'));
    }
}
