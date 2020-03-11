<?php

namespace Chiron\Bootloader;

//use Chiron\Http\Psr\Response;
use Chiron\Http\Response\HtmlResponse;

use Psr\Container\ContainerInterface;
use Chiron\Views\TemplateRendererInterface;
use Chiron\Container\Container;
use Chiron\Bootload\BootloaderInterface;
use LogicException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Chiron\Config\Config;
use Dotenv\Dotenv;
use Dotenv\Environment\DotenvFactory;
use Dotenv\Exception\InvalidFileException;
use Dotenv\Environment\Adapter\PutenvAdapter;
use Dotenv\Environment\Adapter\EnvConstAdapter;
use Dotenv\Environment\Adapter\ServerConstAdapter;
use Chiron\Boot\DirectoriesInterface;
use Chiron\Container\BindingInterface;

//https://github.com/Anlamas/beejee/blob/master/src/Core/Config/ConfigServiceProvider.php

//https://github.com/laravel/lumen-framework/blob/6.x/src/Bootstrap/LoadEnvironmentVariables.php
//https://github.com/viserio/foundation/blob/master/Bootstrap/LoadEnvironmentVariablesBootstrap.php

//https://github.com/laravel/framework/blob/master/src/Illuminate/Foundation/Bootstrap/LoadEnvironmentVariables.php

/**
 * DotEnv service provider. Should be executed before the config service provider !
 */
class DotEnvBootloader implements BootloaderInterface
{

/*
// TODO : vérifier si l'application n'a pas besoin de certaines variables d'environnement. si c'est le cas et qu'il n'y a pas de valeur par défaut il faudra lever une erreur si elles ne sont pas définies et que dotenv n'est pasinstallé (et donc qu'elles ne peuxvent pas être lues depuis un fichier.env).
if (!isset($_SERVER['APP_ENV'])) {
    if (!class_exists(Dotenv::class)) {
        throw new \RuntimeException('APP_ENV environment variable is not defined. You need to define environment variables for configuration or add "vlucas/dotenv" as a Composer dependency to load variables from a .env file.');
    }
    (new Dotenv())->load(__DIR__.'/../.env');
}
*/
    /**
     * @param DirectoriesInterface $directories
     */
    public function boot(DirectoriesInterface $directories)
    {
        if (!class_exists(Dotenv::class)) {
            // if the dotenv library is not installed we get out !
            return;
        }

        try {
            // Load environment file in given directory, silently failing if it doesn't exist.
            $this->createDotenv($directories->get('app'))->safeLoad();
        } catch (InvalidFileException $e) {
            $this->writeErrorAndDie([
                'The environment file (.env) is invalid!',
                $e->getMessage(),
            ]);
        }
    }


    /**
     * Create a Dotenv instance.
     *
     * @return \Dotenv\Dotenv
     */
    protected function createDotenv(string $filePath): Dotenv
    {
        //$info = pathinfo($path);
        //$dotenv = $this->createDotenv($info['dirname'], $info['basename']);

        // default file name for the .enf file.
        $fileName = '.env';

        return Dotenv::create(
            $filePath,
            $fileName,
            new DotenvFactory([new EnvConstAdapter, new PutenvAdapter, new ServerConstAdapter])
        );
    }

    /**
     * Write the error information to the stderr stream and exit.
     *
     * @param  string[]  $lines
     * @return void
     */
    protected function writeErrorAndDie(array $lines): void
    {
        foreach ($lines as $line) {
            error_log($line, 0);
        }

        die(1);
    }
}

