<?php

declare(strict_types=1);

namespace Chiron\ErrorHandler\Formatter;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;

//https://github.com/rudolfcms/rudolf/blob/d7cd824d8c9553fd95eea4570160a153b37fd7c4/src/component/ErrorHandler/Handler/PlainTextHandler.php
class PlainTextFormatter extends AbstractFormatter
{
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

    /**
     * Render Plain-Text error.
     *
     * @param \Throwable $e
     *
     * @return string
     */
    public function format(ServerRequestInterface $request, Throwable $e): string
    {
        return $this->getErrorDetail($e);
    }
}
