<?php

declare(strict_types=1);

namespace Chiron\Dispatcher;

use Chiron\Console\Console;
use Throwable;

/**
 * Manages Console commands and exception. Lazy loads console service.
 */
final class ConsoleDispatcher extends AbstractDispatcher
{
    /**
     * {@inheritdoc}
     */
    public function canDispatch(): bool
    {
        // only run in pure CLI more, ignore under RoadRunner.
        return php_sapi_name() === 'cli' && env('RR') === null;
    }

    /**
     * @param Console $console
     *
     * @return int
     */
    // TODO : il manque le input et ouput pour la console, histoire de pouvoir paramétrer ces valeurs par l'utilisateur (notamment pour les tests)
    protected function perform(Console $console): int
    {
        try {
            return $console->run();
        } catch (Throwable $e) {
            // TODO : il faudrait plutot utiliser le RegisterErrorHandler::renderException($e) pour générer l'affichage de l'exception !!!! Mais attention car cela effectue un die(1), et donc cela va arrété l'application au lieu de retour le code d'erreur 1.
            //$console->handleException($e);
            $this->handleException($e);

            // return the default error code.
            return 1;
        }
    }

    private function handleException(Throwable $e): void
    {
        $message = sprintf(
            "%s %s in %s on line %d\nStack trace:\n%s\n",
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        //$stderr = new StreamOutput(fopen('php://stderr', 'w'));
        //$stderr->write($message);
        fwrite(STDERR, $message);
    }
}
