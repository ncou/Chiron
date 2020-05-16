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

// TODO : on devrait pas en faire une classe générique du style "Collection" ??? car elle n'a rien de fonctionnelle rattaché aux fichiers "publiables"...

// TODO : exemple avec des fonctions du style isEmpty() / contains() ...etc     https://github.com/zendframework/zend-stdlib/blob/master/src/FastPriorityQueue.php

// TODO : il faudra faire un normalizePath sur la source et destination, cela évitera des problémes notamment avec le slash de fin de chaine.
// TODO : renommer la classe en "Publisher" et importer les méthodes de copies des fichiers depuis cette classe. Exemple : ajouter une méthode ->publish() qui copiera les fichiers en utilisant un filesystem
final class PublishableCollection implements IteratorAggregate, Countable
{
    /**
     * @var array
     */
    private $publishable = [];

    // TODO : ajouter un normalizePath sur la source et la destination !!!!
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
