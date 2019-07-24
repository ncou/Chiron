<?php

declare(strict_types=1);

namespace Chiron\Config;

use InvalidArgumentException;
use LogicException;

use ArrayIterator;
use IteratorAggregate;
use ArrayAccess;

/**
 * Generic implementation of array based configuration.
 */
// TODO : il faudrait pas aussi ajouter une interface Countable ???? => https://github.com/slimphp/Slim/blob/3.x/Slim/Collection.php
abstract class AbstractInjectableConfig implements InjectableInterface, IteratorAggregate, ArrayAccess
{
    // TODO : à virer
    //public const INJECTOR = ConfigsInterface::class;

    /**
     * Configuration data.
     *
     * @var array
     */
    protected $config = [];
    /**
     * At this moment on array based configs can be supported.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        // TODO : faire plutot un $this->merge() qu'un remplacement direct de variables !!!!
        $this->config = $config;
        //$this->merge($config);
    }

    //abstract public function getLinkedFile(): string;

    /**
     * @param array $config
     */
    public function merge(array $config): void
    {
        $this->config = $this->recursiveMerge($this->config, $config);
    }

    /**
     * @param mixed $origin
     * @param mixed $appender
     *
     * @return mixed
     */
    // TODO : on dirait que les deux paramétres sont des tableaux. et que la valeur de retour sera aussi un tableau. modifier le typehint
    private function recursiveMerge($origin, $appender)
    {
        if (is_array($origin)
            && array_values($origin) !== $origin
            && is_array($appender)
            && array_values($appender) !== $appender) {
            foreach ($appender as $key => $value) {
                if (isset($origin[$key])) {
                    $origin[$key] = $this->recursiveMerge($origin[$key], $value);
                } else {
                    $origin[$key] = $value;
                }
            }

            return $origin;
        }

        return $appender;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return $this->config;
    }
    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->config);
    }
    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new InvalidArgumentException("Undefined configuration key '{$offset}'");
        }
        return $this->config[$offset];
    }
    /**
     *{@inheritdoc}
     *
     * @throws ConfigException
     */
    public function offsetSet($offset, $value)
    {
        throw new LogicException(
            'Unable to change configuration data, configs are treated as immutable by default'
        );
    }
    /**
     *{@inheritdoc}
     *
     * @throws ConfigException
     */
    public function offsetUnset($offset)
    {
        throw new LogicException(
            'Unable to change configuration data, configs are treated as immutable by default'
        );
    }
    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->config);
    }
    /**
     * Restoring state.
     *
     * @param array $an_array
     *
     * @return static
     */
    // TODO : vérifier l'utilité de cette fonction !!!!
    public static function __set_state($an_array)
    {
        return new static($an_array['config']);
    }

    /**
     * Get number of items in collection
     *
     * @return int
     */
    public function count()
    {
        return count($this->config);
    }
}
