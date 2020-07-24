<?php

namespace Chiron\Bootloader;

use Chiron\Boot\Directories;
use Chiron\Bootload\AbstractBootloader;
use Chiron\PublishableCollection;

final class PublishableCollectionBootloader extends AbstractBootloader
{
    public function boot(PublishableCollection $publishable, Directories $directories): void
    {
        $configPath = __DIR__ . '/../../../config';

        // TODO : faire un tableau avec from et to. et boucler dessus pour faire la copie, cela évitera d'avoir x fois l'instruction $publishable->add() !!!!
        // copy the configuration file template from the package "config" folder to the user "config" folder.
        $publishable->add($configPath . '/app.php.dist', $directories->get('@config/app.php'));
        $publishable->add($configPath . '/settings.php.dist', $directories->get('@config/settings.php'));
        $publishable->add($configPath . '/console.php.dist', $directories->get('@config/console.php'));
        $publishable->add($configPath . '/encrypter.php.dist', $directories->get('@config/encrypter.php'));
        $publishable->add($configPath . '/http.php.dist', $directories->get('@config/http.php'));
    }
}
