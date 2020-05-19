<?php

declare(strict_types=1);

namespace Chiron\Bootloader;

use Chiron\Application;
use Chiron\Config\AppConfig;
use Chiron\Config\CoreConfig;
use Chiron\Bootload\AbstractBootloader;
use Chiron\Dispatcher\ConsoleDispatcher;
use Chiron\Dispatcher\RrDispatcher;
use Chiron\Dispatcher\SapiDispatcher;
use Chiron\Dispatcher\ReactDispatcher;
use Spiral\RoadRunner\PSR7Client;
use Chiron\Container\Container;

// TODO : on devrait pas créer une class "AbstractBootLoader" qui serai une abstract class et qui aurait une méthode getContainer, cad qui aurait dans le constructeur directement le container car on utilise souvent le container, ca éviterai de devoir le passer dans la méthode boot() !!!!
class ApplicationBootloader extends AbstractBootloader
{
    // TODO : lui passer plutot un FactoryInterface en paramétre et non pas un container, ce qui permettrait de faire un "make()" pour créer les classes des dispatchers !!!
    public function boot(Application $application, CoreConfig $coreConfig, AppConfig $appConfig, Container $factory): void
    {


        //die(var_dump($appConfig->getProviders()));

        // TODO : éventuellement séparer en deux ces bootloaders un pour appConfig et l'autre pour coreConfig
        /* --- Application Framework Settings --- */
        foreach ($coreConfig->getProviders() as $provider) {
            $application->addProvider($factory->get($provider));
        }

        foreach ($coreConfig->getBootloaders() as $bootloader) {
            $application->addBootloader($factory->get($bootloader));
        }

        foreach ($coreConfig->getDispatchers() as $dispatcher) {
            $application->addDispatcher($factory->get($dispatcher));
        }

        // TODO : éventuellement séparer en deux ces bootloaders un pour appConfig et l'autre pour coreConfig
        /* --- Application User Settings --- */
        foreach ($appConfig->getProviders() as $provider) {
            $application->addProvider($factory->get($provider));
        }

        foreach ($appConfig->getBootloaders() as $bootloader) {
            $application->addBootloader($factory->get($bootloader));
        }

        foreach ($appConfig->getDispatchers() as $dispatcher) {
            $application->addDispatcher($factory->get($dispatcher));
        }



        //https://github.com/cakephp/app/blob/5b832f14ea9a642b09a9f48da75c9e47bd32e9cd/config/bootstrap.php#L107
        /*
         * Set the default server timezone. Using UTC makes time calculations / conversions easier.
         * Check http://php.net/manual/en/timezones.php for list of valid timezone strings.
         */
        //date_default_timezone_set(Configure::read('App.defaultTimezone'));

        /*
         * Configure the mbstring extension to use the correct encoding.
         */
        //mb_internal_encoding(Configure::read('App.encoding'));

        /*
         * Set the default locale. This controls how dates, number and currency is
         * formatted and sets the default language to use for translations.
         */
        //ini_set('intl.default_locale', Configure::read('App.defaultLocale'));

    }
}
