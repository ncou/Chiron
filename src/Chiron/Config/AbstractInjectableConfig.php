<?php

declare(strict_types=1);

namespace Chiron\Config;

use ArrayAccess;
use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;
use LogicException;

use Nette\Schema\Expect;
use Nette\Schema\Processor;
use Nette\Schema\Schema;
use Nette\Schema\Context;
use Nette\Schema\ValidationException;
use Chiron\Config\Config;
use Chiron\Config\Exception\ConfigException;


//https://github.com/jenssegers/lean/blob/master/src/Slim/Settings.php

/**
 * Generic implementation of array based configuration.
 */
// TODO : il faudrait pas aussi ajouter une interface Countable ???? => https://github.com/slimphp/Slim/blob/3.x/Slim/Collection.php
// TODO : Il faudra que cette méthode get() ou getData() supporte la DotNotation pour aller chercher des clés du genre : getData('buffer.channel1.size')
// TODO : il faudra faire une vérification du modéle de données lorsqu'on appel la méthode injectConfig, voir même une verif lorsqu'on appellera la méthode getData(). Il faudrait éventuellement lever une exception si on manipule une classe de config sans avoir appeller auparavent la méthode injectConfig, car on se retrouvera avec un tableau vide !!!!
abstract class AbstractInjectableConfig extends Config implements InjectableInterface
{
    /** @var string */
    protected const CONFIG_SECTION_NAME = null;

    /**
     * @param array $items
     */
    public function __construct(array $data = [])
    {
        // init the data values (with default scheme values if $data is empty)
        $this->setConfig($data);
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

    abstract protected function getConfigSchema(): Schema;

    public function setConfig(array $data): void
    {
        $this->data = $this->processSchema([$data]);
        $this->cache = [];
    }

    /**
     * Merges (and validates) the current configuration and the new added configuration.
     */
    public function addConfig(array $data): void
    {
        $this->data = $this->processSchema([$this->data, $data]);
        $this->cache = [];
    }

    public function resetConfig(): void
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
        $processor = new Processor;

        try {
            return $processor->processMultiple($schema, $configs);
        } catch (ValidationException $e) {
            throw new ConfigException(sprintf('Schema validation inside %s::class failed [%s]', static::class, $e->getMessage()));
        }
    }
}
