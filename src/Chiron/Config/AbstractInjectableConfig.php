<?php

declare(strict_types=1);

namespace Chiron\Config;

use Chiron\Config\Exception\ConfigException;

//https://github.com/jenssegers/lean/blob/master/src/Slim/Settings.php

/**
 * Generic implementation of array based configuration.
 */
// TODO : il faudrait pas aussi ajouter une interface Countable ???? => https://github.com/slimphp/Slim/blob/3.x/Slim/Collection.php
// TODO : Il faudra que cette méthode get() ou getData() supporte la DotNotation pour aller chercher des clés du genre : getData('buffer.channel1.size')
// TODO : il faudra faire une vérification du modéle de données lorsqu'on appel la méthode injectConfig, voir même une verif lorsqu'on appellera la méthode getData(). Il faudrait éventuellement lever une exception si on manipule une classe de config sans avoir appeller auparavent la méthode injectConfig, car on se retrouvera avec un tableau vide !!!!
// TODO : créer une méthode "inject()" qui sera un proxy pour la méthode setData() ?
// TODO : déplacer cette classe dans le répertoire Boot ?
// TODO : Remplacer les appels à une exception ConfigException par une excetpion ApplicationException ????

// TODO : éventuellement enlever la préfix "abstract" pour le type de classe + dans son nom, si on force l'implémentation de la méthode "getConfigSchema()" en vérifiant que cette méthode existe bien dans la classe mére, sinon on léve une exception. Par contre le probléme c'est que cette classe n'étant plus abstraire pourra être instanciée manuellement par l'utilisateur, il faudra surement ajouter un constructeur privé pour empécher cela !!!!
abstract class AbstractInjectableConfig extends AbstractConfigSchema implements InjectableConfigInterface
{
    /** @var string */
    protected const CONFIG_SECTION_NAME = null;

    /** @var string */
    protected const SECTION_SUBSET_NAME = null;

    public function getConfigSectionName(): string
    {
        // user should redefine the protected value for the section name.
        if (! is_string(static::CONFIG_SECTION_NAME)) {
            // TODO : lever une ApplicationException et non pas une ConfigException !!!! ou alors créer une nouvelle exception du style MissingSectionException qui étendra de la classe ConfigException !!!! (classe à mettre dans le package "core")
            throw new ConfigException(
                sprintf('The config section name should be defined (const %s::CONFIG_SECTION is null)', static::class)
            );
        }
        // handle the case when the user use a directory separator (windows ou linux value) in the linked file path. And remove the starting/ending char '.' if found.
        $section = trim(str_replace(['/', '\\'], '.', static::CONFIG_SECTION_NAME), '.');

        // TODO : tester le comportement quand on ne passe qu'une chaine vide, ou un seul "." qui devient une chaine vide comment se conporte le configManager quand on veux récupérer la section ????
        return $section;
    }

    public function getSectionSubsetName(): ?string
    {
        // TODO : faire une vérification que la constante est soit null soit une string et lever une exception si ce n'est pas le cas ???? ou alors laisser le typehint péter car la valeur de retour ne sera pas bonne ?????
        return static::SECTION_SUBSET_NAME;
    }
}
