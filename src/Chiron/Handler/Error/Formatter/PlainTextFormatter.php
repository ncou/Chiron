<?php

declare(strict_types=1);

namespace Chiron\Handler\Error\Formatter;

use Throwable;

class PlainTextFormatter implements ExceptionFormatterInterface
{
    public function formatException(Throwable $exception, bool $displayErrorDetails): string
    {
        return $this->render($exception, $displayErrorDetails);
    }

    public function render(Throwable $exception, bool $displayErrorDetails): string
    {
        if ($displayErrorDetails) {
            return $this->formatExceptionBody($exception);
        }

        return $exception->getMessage();
    }

    public function formatExceptionBody(Throwable $e): string
    {
        $text = 'Chiron Application Error:' . PHP_EOL;
        $text .= $this->formatExceptionFragment($e);
        while ($e = $e->getPrevious()) {
            $text .= PHP_EOL . 'Previous Error:' . PHP_EOL;
            $text .= $this->formatExceptionFragment($e);
        }

        return $text;
    }

    /**
     * @param \Throwable $e
     *
     * @return string
     */
    // TODO : utiliser la méthode replaceRoot pour le champ "file"
    public function formatExceptionFragment(Throwable $e): string
    {
        $text = sprintf('Type: %s' . PHP_EOL, get_class($e));
        if ($code = $e->getCode()) {
            $text .= sprintf('Code: %s' . PHP_EOL, $code);
        }
        if ($message = $e->getMessage()) {
            // TODO : vérifier l'utilité du htmlentities alors qu'on fait du plain text
            $text .= sprintf('Message: %s' . PHP_EOL, htmlentities($message));
        }
        if ($file = $e->getFile()) {
            $text .= sprintf('File: %s' . PHP_EOL, $file);
        }
        if ($line = $e->getLine()) {
            $text .= sprintf('Line: %s' . PHP_EOL, $line);
        }
        if ($trace = $e->getTraceAsString()) {
            $text .= sprintf('Trace: %s', $trace);
        }

        return $text;
    }
}
