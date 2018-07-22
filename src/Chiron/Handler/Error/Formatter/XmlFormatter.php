<?php

declare(strict_types=1);

namespace Chiron\Handler\Error\Formatter;

use Chiron\Http\Exception\HttpException;
use ErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use UnexpectedValueException;

class XmlFormatter implements ExceptionFormatterInterface
{
    public function formatException(Throwable $exception, bool $displayErrorDetails): string
    {
        return $this->renderXmlBody($exception, $displayErrorDetails);
    }

    /**
     * Render XML error.
     *
     * @param Throwable $error
     *
     * @return string
     */

    // TODO : utiliser la méthode replaceRoot pour le champ "file"
    private function renderXmlBody(Throwable $error, bool $displayErrorDetails): string
    {
        // TODO : virer ce header !!!!!!
        /*
        $xml = "<?xml version='1.0' encoding='UTF-8'?>\n";
        */
        $xml = "";
        $xml .= "<errors>\n  <message>Chiron Application Error</message>\n";

        if ($displayErrorDetails) {
            do {
                $xml .= "  <error>\n";
                $xml .= '    <type>' . get_class($error) . "</type>\n";
                $xml .= '    <code>' . $error->getCode() . "</code>\n";
                $xml .= '    <message>' . $this->createCdataSection($error->getMessage()) . "</message>\n";
                $xml .= '    <file>' . $error->getFile() . "</file>\n";
                $xml .= '    <line>' . $error->getLine() . "</line>\n";
                $xml .= '    <trace>' . $this->createCdataSection($error->getTraceAsString()) . "</trace>\n";
                $xml .= "  </error>\n";
            } while ($error = $error->getPrevious());
        }
        $xml .= '</errors>';

        return $xml;
    }

    /**
     * Returns a CDATA section with the given content.
     *
     * @param string $content
     *
     * @return string
     */
    private function createCdataSection(string $content): string
    {
        return sprintf('<![CDATA[%s]]>', str_replace(']]>', ']]]]><![CDATA[>', $content));
    }
}
