<?php

declare(strict_types=1);

namespace Chiron\Config;

//https://github.com/Wandu/Framework/blob/master/src/Wandu/Config/Config.php

//https://github.com/zendframework/zend-config/blob/master/src/Config.php
// TODO : regarder ici : https://github.com/zendframework/zf3-web/tree/master/config

//https://github.com/zendframework/zend-config-aggregator/blob/master/src/ConfigAggregator.php

//https://github.com/PHLAK/Config/blob/master/src/Config.php
//https://github.com/hassankhan/config/blob/master/src/AbstractConfig.php   +    https://github.com/hassankhan/config/blob/master/src/Config.php
//https://github.com/mrjgreen/config/blob/master/src/Config/Repository.php
//https://github.com/pinepain/php-simple-config/blob/master/src/Config.php

//https://github.com/adbario/php-dot-notation/blob/2.x/src/Dot.php

class Config implements \ArrayAccess, \Iterator, \Countable
{
    /**
     * Data within the configuration.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Used when unsetting values during iteration to ensure we do not skip
     * the next element.
     *
     * @var bool
     */
    protected $skipNextIteration;

    /**
     * Class constructor, runs on object creation.
     *
     * @param array $data Raw array of configuration options
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Retrieve a configuration option via a provided key.
     *
     * @param string $key     Unique configuration option key
     * @param mixed  $default Default value to return if option does not exist
     *
     * @return mixed Stored config item or $default value
     */
    public function get(string $key, $default = null)
    {
        $data = $this->data;
        $keys = explode('.', $key);

        foreach ($keys as $k) {
            if (! isset($data[$k])) {
                //if (!is_array($data) || !array_key_exists($k, $data)) {
                return $default;
            }
            $data = $data[$k];
        }

        return $data;
    }

    /**
     * Store a config value with a specified key.
     *
     * @param string $key   Unique configuration option key
     * @param mixed  $value Config item value
     *
     * @return object This Config object
     */
    public function set(string $key, $value)
    {
        $data = &$this->data;
        $keys = explode('.', $key);

        foreach ($keys as $k) {
            $data = &$data[$k];
        }
        $data = $value;

        return $this;
    }

    /**
     * Check for the existance of a config item.
     *
     * @param string $key Unique configuration option key
     *
     * @return bool True if item existst, otherwise false
     */
    public function has(string $key): bool
    {
        $data = $this->data;
        $keys = explode('.', $key);

        foreach ($keys as $k) {
            if (! isset($data[$k])) {
                return false;
            }
            $data = $data[$k];
        }

        return true;
    }

    /**
     * Remove a value using the offset as a key.
     *
     * @param string $key
     *
     * @return object This Config object
     */
    public function remove($key)
    {
        // @TODO : faire en sorte de pouvoir supprimer des clés du type ->remove('php.settings.abc') au lieu de mettre cela à null pour l'instant. Il faudra aussi gérer le risque de désynchro si on fait un unset ou un remove pendant une boucle.
        $this->set($key, null);

        return $this;
    }

    /**
     * Load configuration options from an array.
     *
     * @param array $data     Raw array of configuration options
     * @param bool  $override Whether or not to override existing options with
     *                        values from the loaded array data
     *
     * @return object This Config object
     */
    // TODO : renommer en mergeConfigArray($array) + créer une méthode mergeConfigFrom($filepath) ou même mergeConfigFile($path) cf : https://laracasts.com/discuss/channels/general-discussion/how-does-mergeconfigfrom-work
    public function merge(array $data, bool $override = true)
    {
        if ($override) {
            $this->data = array_merge($this->data, $data);
        } else {
            $this->data = array_merge($data, $this->data);
        }

        return $this;
    }

    /**
     * Get all of the configuration items.
     *
     * @return array
     */
    public function all()
    {
        return $this->data;
    }

    /*******************************************************************************
     * ArrayAccess Methods
     ******************************************************************************/

    /**
     * Determine whether an item exists at a specific offset.
     *
     * @param int $offset Offset to check for existence
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        //return $this->has($offset);
        return $this->__isset($offset);
    }

    /**
     * Retrieve an item at a specific offset.
     *
     * @param int $offset Position of character to get
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        //return $this->get($offset);
        return $this->__get($offset);
    }

    /**
     * Assign a value to the specified item at a specific offset.
     *
     * @param mixed $offset The offset to assign the value to
     * @param mixed $value  The value to set
     */
    public function offsetSet($offset, $value)
    {
        //$this->set($offset, $value);
        $this->__set($offset, $value);
    }

    /**
     * Unset an item at a specific offset.
     *
     * @param $offset The offset to unset
     */
    public function offsetUnset($offset)
    {
        //$this->remove($offset);
        $this->__unset($offset);
    }

    /*******************************************************************************
     * Magic Methods
     ******************************************************************************/

    /**
     * Magic function so that $obj->value will work.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Set a value in the config.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @throws Exception\RuntimeException
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);

        // @TODO : utiliser le code ci dessous pour gérer le cas ou $config = [] cad quand le $name n'est pas utilisé.
        // @TODO : gerer le cas ou on a une chaine avec des points, il faut créer un tableau ou non ???? genre $config->php = 'date.timezone' ou 'test123'
        /*
        if (is_array($value)) {
            $value = new static($value, true);
        }
        if (null === $name) {
            $this->data[] = $value;
        } else {
            $this->data[$name] = $value;
        }*/
    }

    /**
     * isset() overloading.
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return $this->has($name);

        //return isset($this->data[$name]);
    }

    /**
     * unset() overloading.
     *
     * @param string $name
     *
     * @throws Exception\InvalidArgumentException
     */
    public function __unset($name)
    {
        $this->remove($name);
        /*
        if (isset($this->data[$name])) {
            unset($this->data[$name]);
            $this->skipNextIteration = true;
        }*/
    }

    /**
     * Deep clone of this instance to ensure that nested Chiron\Configs are also cloned.
     */
    public function __clone()
    {
        $array = [];
        foreach ($this->data as $key => $value) {
            if ($value instanceof self) {
                $array[$key] = clone $value;
            } else {
                $array[$key] = $value;
            }
        }
        $this->data = $array;
    }

    /*******************************************************************************
     * Iterator Methods
     ******************************************************************************/

    /**
     * Returns the config array element referenced by its internal cursor.
     *
     * @return mixed The element referenced by the config array's internal cursor.
     *               If the array is empty or there is no element at the cursor, the
     *               function returns false. If the array is undefined, the function
     *               returns null
     */
    public function current()
    {
        $this->skipNextIteration = false;

        return current($this->data);
    }

    /**
     * Returns the config array index referenced by its internal cursor.
     *
     * @return mixed The index referenced by the config array's internal cursor.
     *               If the array is empty or undefined or there is no element at the
     *               cursor, the function returns null
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * Moves the config array's internal cursor forward one element.
     *
     * @return mixed The element referenced by the config array's internal cursor
     *               after the move is completed. If there are no more elements in the
     *               array after the move, the function returns false. If the config array
     *               is undefined, the function returns null
     */
    public function next()
    {
        if ($this->skipNextIteration) {
            $this->skipNextIteration = false;

            return;
        }

        return next($this->data);
    }

    /**
     * Moves the config array's internal cursor to the first element.
     *
     * @return mixed The element referenced by the config array's internal cursor
     *               after the move is completed. If the config array is empty, the function
     *               returns false. If the config array is undefined, the function returns
     *               null
     */
    public function rewind()
    {
        $this->skipNextIteration = false;

        return reset($this->data);
    }

    /**
     * Tests whether the iterator's current index is valid.
     *
     * @return bool True if the current index is valid; false otherwise
     */
    public function valid()
    {
        return key($this->data) !== null;
    }

    /*******************************************************************************
     * Count Methods
     ******************************************************************************/

    /**
     * count(): defined by Countable interface.
     *
     * @see    Countable::count()
     *
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }
}
