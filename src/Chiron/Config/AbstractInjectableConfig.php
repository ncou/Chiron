<?php

declare(strict_types=1);

namespace Chiron\Config;

use Chiron\Config\Exception\ConfigException;
use Closure;
use Nette\Schema\Processor;
use Nette\Schema\Schema;

//https://github.com/jenssegers/lean/blob/master/src/Slim/Settings.php

/**
 * Generic implementation of array based configuration.
 */
// TODO : il faudrait pas aussi ajouter une interface Countable ???? => https://github.com/slimphp/Slim/blob/3.x/Slim/Collection.php
// TODO : Il faudra que cette méthode get() ou getData() supporte la DotNotation pour aller chercher des clés du genre : getData('buffer.channel1.size')
// TODO : il faudra faire une vérification du modéle de données lorsqu'on appel la méthode injectConfig, voir même une verif lorsqu'on appellera la méthode getData(). Il faudrait éventuellement lever une exception si on manipule une classe de config sans avoir appeller auparavent la méthode injectConfig, car on se retrouvera avec un tableau vide !!!!
// TODO : séparezr cette classe en deux classes, une plutot générique pour la gestion des configs qui sont validées par le schéma (cad sans les méthodes getConfigSectionName et getSectionSubsetName) et la déplacer dans le package Config + une classe orienté projet avec les 2 méthodes getConfigSectionname et getSectionSubsetName + une méthode inject qui sera un proxy pour la méthode setData(), et mettre cette classe dans le répertoire Boot. Et remplacer les appels à une exception ConfigException par une excetpion ApplicationException
abstract class AbstractInjectableConfig extends Config implements InjectableConfigInterface
{
    /** @var string */
    protected const CONFIG_SECTION_NAME = null;

    /** @var string */
    protected const SECTION_SUBSET_NAME = null;

    /**
     * @param array $items
     */
    public function __construct(array $data = [])
    {
        // init the data values (with default scheme values if $data is empty)
        $this->setData($data);
    }

    public function getConfigSectionName(): string
    {
        // user should redefine the protected value for the section name.
        if (! is_string(static::CONFIG_SECTION_NAME)) {
            throw new ConfigException(sprintf('The config section name should be defined (const %s::CONFIG_SECTION is missing)', static::class));
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

    abstract protected function getConfigSchema(): Schema;

    public function setData(array $data): void
    {
        $this->data = $this->processSchema([$data]);
        $this->cache = [];
    }

    /**
     * Merges (and validates) the current configuration and the new added configuration.
     */
    public function addData(array $data): void
    {
        $this->data = $this->processSchema([$this->data, $data]);
        $this->cache = [];
    }

    public function resetData(): void
    {
        $this->data = $this->processSchema([]);
        $this->cache = [];
    }

    /**
     * Merges and validates configurations against scheme.
     *
     * @return array
     */
    private function processSchema(array $configs): array
    {
        // Force the return value to be an array (by default the processed schema return an stdObject)
        $schema = $this->getConfigSchema()->castTo('array');
        $processor = new Processor();

        try {
            return $processor->processMultiple($schema, $configs);
        } catch (\Nette\Schema\ValidationException $e) {
            // TODO : faire une reflection de la méthode getConfigSchema pour afficher dans l'exception (en faisant un replace de $line et $file) l'endroit ou cela a planté ???
            throw new ConfigException(sprintf('Schema validation inside %s::class failed [%s]', static::class, $e->getMessage()));
        }
    }

    /**
     * Helper used for the Nette/Schema assert() validation.
     * Check if the array is associative (all keys should be strings).
     *
     * @return Closure
     */
    protected static function isArrayAssociative(): Closure
    {
        return function (array $array): bool {
            return count(array_filter(array_keys($array), 'is_string')) === count($array);
        };
    }

    /**
     * Helper used for the Nette/Schema assert() validation.
     * Check if the array is a zero-based integer indexed array.
     *
     * @return Closure
     */
    protected static function isArrayIndexed(): Closure
    {
        return function (array $array): bool {
            return array_keys($array) === range(0, count($array) - 1);
        };
    }
}
