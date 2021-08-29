<?php

declare(strict_types=1);

namespace Chiron\Config;

use Chiron\Config\AbstractInjectableConfig;
use Chiron\Config\Helper\Validator;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

// TODO : Exemple d'une classe pour le suivi des Environment  :   https://github.com/NigelGreenway/reactive-slim/blob/af7cbf00effc65b44a050c7ebc41850dd1f255f0/src/ServerEnvironment.php#L5

// TODO : déplacer la partie "Settings" dans le package chiron/core car ca peut être utilisé par un bootloader ou un middleware en dehors  de l'application chiron !!!!
// TODO : eventuellement renommer tous ce qui touche à "Settings" avec le nom "Core", ex: core.php.dist au lieu de settings.php.dist
final class SettingsConfig extends AbstractInjectableConfig
{
    protected const CONFIG_SECTION_NAME = 'settings';

    protected function getConfigSchema(): Schema
    {
        // TODO : ajouter un champ "name" qui contiendrait le nom de l'application ??? et éventuellement un champ "version" ????
        // TODO : ajouter un champ "environment" ??? qui contiendrait les veleurs "developement" / "production" par exemple !!!!
        return Expect::structure([
            'debug'       => Expect::boolean()->default(env('APP_DEBUG', false)),
            // TODO : code temporaire à améliorer !!!!
            'environment' => Expect::string()->default('production'),
            'name'        => Expect::string()->default('CHIRON'),

            'charset'     => Expect::string()->assert([Validator::class, 'isCharset'], 'charset')->default(env('APP_ENCODING', 'UTF-8')),
            'locale'      => Expect::string()->assert([Validator::class, 'isLocale'], 'locale')->default(env('APP_DEFAULT_LOCALE', 'en_US')),
            'timezone'    => Expect::string()->assert([Validator::class, 'isTimezone'], 'timezone')->default(env('APP_DEFAULT_TIMEZONE', 'UTC')),
        ])->otherItems(Expect::mixed()); // TODO : voir si on doit conserver la fonction otherItems !!!! cad si on peut ajouter d'autres balises librement dans le fichier de config des settings !!!!
    }

    // TODO : conserver cette méthode (sachant que la méthode isDebug() est mieux !!!!) ????
    public function getDebug(): bool
    {
        return $this->get('debug');
    }

    public function isDebug(): bool
    {
        return $this->get('debug');
    }

    public function getCharset(): string
    {
        return $this->get('charset');
    }

    public function getLocale(): string
    {
        return $this->get('locale');
    }

    public function getTimezone(): string
    {
        return $this->get('timezone');
    }

    /**
     * Asserts that the locale is valid, throws an Exception if not.
     *
     * @throws InvalidArgumentException If the locale contains invalid characters
     */
    // TODO : utiliser cette fonction pour vérifier que la locale est bien valide !!!!  https://github.com/symfony/translation/blob/5.x/Translator.php#L447
    /*
    private function assertValidLocale(string $locale)
    {
        if (1 !== preg_match('/^[a-z0-9@_\\.\\-]*$/i', $locale)) {
            throw new InvalidArgumentException(sprintf('Invalid "%s" locale.', $locale));
        }
    }*/
}
