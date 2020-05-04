<?php

declare(strict_types=1);

namespace Chiron;

use Chiron\Boot\Directories;
use Chiron\Boot\Filesystem;
use RuntimeException;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Transversable;


// TODO : exemple avec des fonctions du style isEmpty() / contains() ...etc     https://github.com/zendframework/zend-stdlib/blob/master/src/FastPriorityQueue.php

final class PublishableCollection implements IteratorAggregate, Countable
{
    /**
     * @var array
     */
    private $publishable = [];

    public function add(string $source, string $destination)
    {
        $this->publishable[$source] = $destination;
    }

    public function toArray(): array
    {
        return $this->publishable;
    }

    /**
     * Get the number of publishable item in the collection.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->publishable);
    }

    /**
     * Gets the iterator
     *
     * @return ArrayIterator
     */
    public function getIterator()//: Transversable
    {
        return new ArrayIterator($this->publishable);
    }


}
