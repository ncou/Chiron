<?php

namespace Chiron\Bootloader;

use Chiron\Bootload\AbstractBootloader;
use Chiron\Boot\Directories;
use Chiron\PublishableCollection;

class PublishableCollectionBootloader extends AbstractBootloader
{
    public function boot(PublishableCollection $publishable, Directories $directories)
    {
        // TODO : créer une variable pour avoir le répertoire de base du framework et donc éviter ce type d'écriture (__DIR__ et les '/../')
        $publishable->add(__DIR__.'/../../../config/test_config.php', $directories->get('config'). '/test_config.php');

        $publishable->add(__DIR__.'/../../../config/toto', $directories->get('config'). '/toto');

        $publishable->add('/foobar/test_config2.php', $directories->get('config'). '/test_config2.php');
    }
}
