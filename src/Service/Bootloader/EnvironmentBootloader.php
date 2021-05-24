<?php

namespace Chiron\Service\Bootloader;

use Chiron\Core\Directories;
use Chiron\Core\Environment;
use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Config\Config;
use Chiron\Core\Exception\EnvironmentException;
use Dotenv\Dotenv;
use Dotenv\Exception\InvalidFileException;

//https://github.com/swoft-cloud/swoft-framework/blob/c222ba60ce2463c60926d1cf0209309c1844eb8c/src/Processor/EnvProcessor.php

//https://github.com/Anlamas/beejee/blob/master/src/Core/Config/ConfigServiceProvider.php

//https://github.com/laravel/lumen-framework/blob/6.x/src/Bootstrap/LoadEnvironmentVariables.php
//https://github.com/viserio/foundation/blob/master/Bootstrap/LoadEnvironmentVariablesBootstrap.php

//https://github.com/laravel/framework/blob/master/src/Illuminate/Foundation/Bootstrap/LoadEnvironmentVariables.php

//https://github.com/symfony/symfony/blob/8b337fc94a97f7c74ca989e8049113abf0e30c83/src/Symfony/Component/Dotenv/Tests/DotenvTest.php#L378

// TODO : faire la montée de version en v5.0 de vlucas/dotenv.
// TODO : déplacer le fichier .env à la racine du projet (@root) et non pas dans le répertoire @app !!!!!!!!!!!!!!!! Penser à modifier le script composer qui copie le fichier .env.example
final class EnvironmentBootloader extends AbstractBootloader
{
    /** @var string */
    public const DOTENV = 'CHIRON_DOTENV_VARS';

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
        $loadedVars = self::loadDotEnvFile($directories->get('@root'));

        // store the vars present in the dotenv file to display them when using the AboutCommand.
        $this->values[self::DOTENV] = $loadedVars;
        //$_ENV['SYMFONY_DOTENV_VARS'] = $_SERVER['SYMFONY_DOTENV_VARS'] = $loadedVars;

        // initialise the environment values (using array $this->values as override).
        $environment->init($this->values);


        //die(var_dump(env('APP_KEY')));
        //die(var_dump(container(Environment::class)->get('APP_KEY')));

        // TODO : on devrait pas faire une vérification que les variable d'environnement de base sont bien présentes ? ca peut arriver si l'utilisateur n'utilise pas dotenv (ou que le fichier .env est effacé) et que les variables d'environnement n'existent pas sur la machine. Par exemple si la variable APP_KEY n'existe pas, on aura un plantage lors du changement de clés via la commande encrypt:key !!!!
    }

    /**
     * Read the dot env file and insert the values in $_ENV and $_SERVER.
     * If the dot env file isn't presents the load fail silently and return an empty array.
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

        $dotenv = Dotenv::createImmutable($path, '.env');

        // TODO : vérifier que cela retourne bien un tableau vide si le fichier .env n'est pas trouvé (cad lors du silent fail).
        try {
            // Load environment file in given directory path, silently failing if it doesn't exist.
            return $dotenv->safeLoad();
        } catch (InvalidFileException $e) {
            throw new EnvironmentException('The environment file (.env) is invalid!');
        }
    }
}
