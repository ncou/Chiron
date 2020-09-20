<?php

declare(strict_types=1);

namespace Chiron\Bootloader;

use Chiron\Application;
use Chiron\Bootload\AbstractBootloader;

final class NoBootingBootloader extends AbstractBootloader
{
    // TODO : lui passer plutot un Environment::class ???? pour aller chercher la valeur de argv ????
    public function boot(): void
    {
        // TODO : c'est un test !!!! code à virer ou à nettoyer plus tard !!!!
        if (php_sapi_name() === 'cli') {
            $argv = $_SERVER['argv'];

            /*
            $args = array_slice($argv, 1);
            die(var_dump($args));
            */

            // strip the application name
            array_shift($argv);

            $tokens = $argv;

            //die(var_dump($tokens));

            $res = in_array('--no-boot', $tokens);

            //die(var_dump($res));

            if ($res === true) {
                $_SERVER['NO-BOOT'] = 'true';
            }
        }
    }
}
