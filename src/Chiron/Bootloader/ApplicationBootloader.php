<?php

declare(strict_types=1);

namespace Chiron\Bootloader;

use Chiron\Application;
use Chiron\Bootload\AbstractBootloader;
use Chiron\Config\AppConfig;
use Chiron\Config\CoreConfig;
use Chiron\Container\Container;

final class ApplicationBootloader extends AbstractBootloader
{
    // TODO : lui passer plutot un FactoryInterface en paramétre et non pas un container, ce qui permettrait de faire un "make()" pour créer les classes des providers/bootloaders/commands...etc !!!
    public function boot(Application $application, AppConfig $appConfig, Container $factory): void
    {
        foreach ($appConfig->getProviders() as $provider) {
            $application->addProvider($factory->get($provider));
        }

        foreach ($appConfig->getBootloaders() as $bootloader) {
            $application->addBootloader($factory->get($bootloader));
        }

        foreach ($appConfig->getDispatchers() as $dispatcher) {
            $application->addDispatcher($factory->get($dispatcher));
        }

        // TODO : il faudra aussi pouvoir ajouter les mutations et les commands dans le fichier de configuration app.php ??? non ???


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



        //date_default_timezone_set('UTC');
        //setlocale(LC_ALL, 'C.UTF-8');
        //mb_internal_encoding('UTF-8');
    }
}
