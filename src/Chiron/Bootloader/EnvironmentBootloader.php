<?php

namespace Chiron\Bootloader;

use Chiron\Boot\Directories;
use Chiron\Boot\Environment;
use Chiron\Bootload\AbstractBootloader;
use Chiron\Config\Config;
use Dotenv\Dotenv;
use Dotenv\Environment\Adapter\EnvConstAdapter;
use Dotenv\Environment\Adapter\PutenvAdapter;
use Dotenv\Environment\Adapter\ServerConstAdapter;
use Dotenv\Environment\DotenvFactory;
use Dotenv\Exception\InvalidFileException;
use Chiron\Exception\ApplicationException;

//https://github.com/swoft-cloud/swoft-framework/blob/c222ba60ce2463c60926d1cf0209309c1844eb8c/src/Processor/EnvProcessor.php

//https://github.com/Anlamas/beejee/blob/master/src/Core/Config/ConfigServiceProvider.php

//https://github.com/laravel/lumen-framework/blob/6.x/src/Bootstrap/LoadEnvironmentVariables.php
//https://github.com/viserio/foundation/blob/master/Bootstrap/LoadEnvironmentVariablesBootstrap.php

//https://github.com/laravel/framework/blob/master/src/Illuminate/Foundation/Bootstrap/LoadEnvironmentVariables.php

//https://github.com/symfony/symfony/blob/8b337fc94a97f7c74ca989e8049113abf0e30c83/src/Symfony/Component/Dotenv/Tests/DotenvTest.php#L378

final class EnvironmentBootloader extends AbstractBootloader
{
    /** @var array */
    private $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * @param Environment $environment
     * @param Directories $directories
     */
    public function boot(Environment $environment, Directories $directories): void
    {
        $loadedVars = self::loadDotEnvFile($directories->get('@app'));
        // store the keys present in the dotenv file to display them when using the AboutCommand.
        $this->values['CHIRON_DOTENV_VARS'] = $loadedVars;
        // initialise the environment values (using array $this->values as override).
        $environment->init($this->values);
    }

    /**
     * Read the dot env file and insert the values in $_ENV and $_SERVER.
     *
     * @param string $path The directory path containing the dot env file.
     *
     * @return array The keys/values readed in the dot env file
     */
    private static function loadDotEnvFile(string $path): array
    {
        if (! class_exists(Dotenv::class)) {
            return [];
        }

        try {
            // Load environment file in given directory path, silently failing if it doesn't exist.
            return self::createDotenv($path)->safeLoad();
        } catch (InvalidFileException $e) {
            throw new ApplicationException('The environment file (.env) is invalid!');
        }
    }

    /**
     * Create a Dotenv instance.
     *
     * @param string $path
     *
     * @return \Dotenv\Dotenv
     */
    private static function createDotenv(string $path): Dotenv
    {
        // TODO : il faudrait vérifier si il y a une variable d'environnement qui définie le nom du fichier (.env) à lire ?
        // default file name for the .env file. => ca ne marchera pas car on n'a pas encore chargé les variables d'environnement qui sont dans le .env !!!!
        $name = '.env';

        return Dotenv::create(
            $path,
            $name,
            new DotenvFactory([new ServerConstAdapter(), new EnvConstAdapter()])
        );
    }

        /*
    // TODO : vérifier si l'application n'a pas besoin de certaines variables d'environnement. si c'est le cas et qu'il n'y a pas de valeur par défaut il faudra lever une erreur si elles ne sont pas définies et que dotenv n'est pasinstallé (et donc qu'elles ne peuvent pas être lues depuis un fichier.env).
    if (!isset($_SERVER['APP_ENV'])) {
        if (!class_exists(Dotenv::class)) {
            throw new \RuntimeException('APP_ENV environment variable is not defined. You need to define environment variables for configuration or add "vlucas/dotenv" as a Composer dependency to load variables from a .env file.');
        }
        (new Dotenv())->load(__DIR__.'/../.env');
    }
    */
}
