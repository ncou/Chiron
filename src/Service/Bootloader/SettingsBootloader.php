<?php

declare(strict_types=1);

namespace Chiron\Service\Bootloader;

use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Core\Config\SettingsConfig;

//https://github.com/cakephp/app/blob/master/config/bootstrap.php

final class SettingsBootloader extends AbstractBootloader
{
    // TODO : faire un gros try/catch sur les exceptions Throwable pour les 3 instructions ? et lever une ApplicationException si on rencontre une erreur ????
    // TODO : on fait rien avec le booléen debug ???? on devrait pas initialiser un error handler ou un truc dans le genre ???
    public function boot(SettingsConfig $settings): void
    {
        /*
         * Configure the mbstring extension to use the correct encoding.
         */
        // TODO : ajouter un try/catch
        mb_internal_encoding($settings->getCharset());

        /*
         * Set the default server timezone. Using UTC makes time calculations / conversions easier.
         * Check http://php.net/manual/en/timezones.php for list of valid timezone strings.
         */
        // TODO : ajouter un try/catch
        date_default_timezone_set($settings->getTimezone());

        /*
         * Set the default locale. This controls how dates, number and currency is
         * formatted and sets the default language to use for translations.
         */
        // TODO : cette méthode ne retourne pas d'exception même dans le cas ou la locale est invalide :-(
        locale_set_default($settings->getLocale());
        // TODO : on devrait pas faire un setlocale plutot ??? et surtout si le retour est faux on pourra lever une exception !!!!!
        //ini_set('intl.default_locale', $settings->getLocale());

        //ini_get('default_charset')
        //locale_get_default()
        //date_default_timezone_get()

        //date_default_timezone_set('UTC');
        //setlocale(LC_ALL, 'C.UTF-8');
        //mb_internal_encoding('UTF-8');

        // TODO : on peut utiliser cette fonctiuon : locale_set_default('XXXXX'); ????

/*

        //Set runtime locale information for date and time formatting
        setlocale(LC_TIME, $language);
        //Sets the default runtime locale


        /**
 * Set the default locale. This controls how dates, number and currency is
 * formatted and sets the default language to use for translations.
 */
        /*
        locale_set_default($language);
        */
    }
}
