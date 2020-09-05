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
        // only run in pure CLI more, ignore under RoadRunner/ReactPhp.
        return PHP_SAPI === 'cli' && env('RR') === null && env('REACT_PHP') === null && env('WORKER_MAN') === null;
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

    // TODO : externaliser ou utiliser le ErrorHandler pour gérer l'affichage de l'erreur => https://github.com/filp/whoops/blob/96b540726286e4d8f64f68efe6b260c8b4a00d6d/src/Whoops/Handler/PlainTextHandler.php
    private function handleException(Throwable $exception): void
    {
        $message = $this->getExceptionOutput($exception);

        $previous = $exception->getPrevious();
        while ($previous) {
            $message .= "\n\nCaused by:\n" . $this->getExceptionOutput($previous) . "\n";
            $previous = $previous->getPrevious();
        }

        $message .= "\nStack trace:\n" . $exception->getTraceAsString() . "\n";


        //$stderr = new StreamOutput(fopen('php://stderr', 'w'));
        //$stderr->write($message);
        fwrite(STDERR, $message);
    }

    /**
     * Get the exception as plain text.
     * @param \Throwable $exception
     * @return string
     */
    private function getExceptionOutput(Throwable $exception): string
    {
        return sprintf(
            "%s: %s in file %s on line %d",
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
    }
}
