<?php

declare(strict_types=1);

namespace Chiron\Engine;

use Chiron\Console\Console;
use Chiron\Core\Engine\AbstractEngine;
use Throwable;

// TODO : utiliser ce code pour afficher les exceptions dans la console : https://github.com/webmozart/console/blob/master/src/UI/Component/ExceptionTrace.php

// TODO : essayer de rendre paramétrable les valeurs qui ne sont pas considérées comme un mode "pure" console (à réfléchir si c'est une bonne idée !!!).

// TODO : déplacer cette classe dans le package chiron/core ????

/**
 * Manages Console commands and exception. Lazy loads console service.
 */
// TODO : déplacer dans le package chiron/chiron ???? car les dispatchers n'ont pas d'utilité hors du package qui contient la classe Application !!!! Ca serait encore plus logique car il y a le package debug dans le package chiron donc on pourra afficher les exception en utilisant un ConsoleErrorHandler (qui utilisera collision pour afficher en détail l'exception).
final class ConsoleEngine extends AbstractEngine
{
    /**
     * {@inheritdoc}
     */
    public function isActive(): bool
    {
        // only run in pure CLI more, ignore under RoadRunner/ReactPhp/WorkerMan.
        return PHP_SAPI === 'cli'
            && env('RR') === null
            && env('REACT_PHP') === null
            && env('WORKER_MAN') === null;
    }

    /**
     * @param Console $console
     *
     * @return int
     */
    protected function perform(Console $console): int
    {
        // TODO : initialiser un Input et Ouput à passer en paramétre de run($input, $output) ???? ou ce n'est pas la peine ????
        return $console->run();
    }
}
