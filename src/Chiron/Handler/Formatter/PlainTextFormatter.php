<?php

declare(strict_types=1);

namespace Chiron\Handler\Formatter;

use Chiron\Handler\ExceptionInfo;
use Chiron\Http\Exception\HttpException;
use Throwable;

class PlainTextFormatter implements FormatterInterface
{
    // TODO : permettre de passer cette valeur en paramétre dans le constructeur de la classe.
    private $includeStacktraces = false;

    /**
     * Render Plain-Text error.
     *
     * @param \Throwable $e
     *
     * @return string
     */
    public function format(Throwable $e): string
    {
        $text = $this->formatException($e);

        if ($previous = $e->getPrevious()) {
            do {
                $text .= "\n[previous exception] " . $this->formatException($previous);
            } while ($previous = $previous->getPrevious());
        }

        return $text;
    }

    /**
     * @param \Throwable $e
     *
     * @return string
     */
    private function formatException(\Throwable $e): string
    {
        $str = '[object] (' . get_class($e) . '(code: ' . $e->getCode();

        if ($e instanceof \SoapFault) {
            if (isset($e->faultcode)) {
                $str .= ' faultcode: ' . $e->faultcode;
            }
            if (isset($e->faultactor)) {
                $str .= ' faultactor: ' . $e->faultactor;
            }
            if (isset($e->detail)) {
                $str .= ' detail: ' . $e->detail;
            }
        }

        $str .= '): ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine() . ')';

        if ($this->includeStacktraces) {
            $str .= "\n[stacktrace]\n" . $e->getTraceAsString() . "\n";
        }

        return $str;
    }

    /**
     * Get the supported content type.
     *
     * @return string
     */
    public function contentType(): string
    {
        return 'text/plain';
    }

    /**
     * Do we provide verbose information about the exception?
     *
     * @return bool
     */
    public function isVerbose(): bool
    {
        // TODO : conditionner l'affichage de la stackstrace avec la valeur de ce booléen (qui représente le debug = true ou false).
        return false;
    }

    /**
     * Can we format the exception?
     *
     * @param \Throwable $e
     *
     * @return bool
     */
    public function canFormat(Throwable $e): bool
    {
        return true;
    }

}
