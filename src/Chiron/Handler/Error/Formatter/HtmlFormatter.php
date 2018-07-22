<?php

declare(strict_types=1);

namespace Chiron\Handler\Error\Formatter;

use Chiron\Http\Exception\HttpException;
use ErrorException;
use Throwable;

//https://github.com/userfrosting/UserFrosting/blob/master/app/sprinkles/core/src/Error/Renderer/HtmlRenderer.php

class HtmlFormatter implements ExceptionFormatterInterface
{
    public function formatException(Throwable $exception, bool $displayErrorDetails): string
    {
        return $this->renderHtmlBody($exception, $displayErrorDetails);
    }

    /**
     * Render HTML error page.
     *
     * @param Chiron\Http\Exception\HttpException $error
     *
     * @return string
     */
    private function renderHtmlBody(Throwable $error, bool $displayErrorDetails)
    {
        $title = 'Chiron Error';
        $html = '<p class="lead">Whoops, looks like something went wrong.</p>';

        if ($displayErrorDetails) {
            $html .= '<h2>&bull; Error Details</h2>';
            $html .= $this->renderThrowableFragment($error);
            while ($error = $error->getPrevious()) {
                $html .= '<h2>&bull; Previous Error</h2>';
                $html .= $this->renderThrowableFragment($error);
            }
        }

        return sprintf(
            '<html>' .
            '   <head>' .
            "       <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>" .
            '       <title>%s</title>' .
            '       <style>' .
            '           body{margin:0;padding:20px;font-family:Helvetica,Arial,Verdana,sans-serif;font-size:15px;line-height:1.5em}' .
            '           h1{margin:0;font-size:40px;font-weight:normal;line-height:40px;padding-bottom: 10px;border-bottom:1px solid #eee}' .
            '           p.lead{font-size:22px}' .
            '           strong{display:inline-block;width:85px}' .
            '           table{border-spacing:0;border-collapse:collapse}' .
            '           table tbody tr td{padding:8px;line-height:1.5em;vertical-align:middle;border-top:1px solid #ddd;font-family:monospace}' .
            '           table>tbody>tr:nth-child(odd)>td{background-color:#f9f9f9}' .
            '       </style>' .
            '   </head>' .
            '   <body>' .
            '       <h1>%s</h1>' .
            '       <div>%s</div>' .
            "       <a href='#' onClick='window.history.go(-1)'>Go Back</a>" .
            '   </body>' .
            '</html>',
            $this->escapeHtml($title),
            $this->escapeHtml($title),
            $html
        );
    }

    /**
     * Render error as HTML.
     *
     * @param Throwable $error
     *
     * @return string
     */
    private function renderThrowableFragment(Throwable $e): string
    {
        $html = sprintf('<div><strong>Type:</strong> %s (%s)</div>', $this->escapeHtml(get_class($e)), $this->getExceptionCode($e));

        //if (($code = $e->getCode())) {
        //    $html .= sprintf('<div><strong>Code:</strong> %s</div>', $code);
        //}
        if (($message = $e->getMessage())) {
            $html .= sprintf('<div><strong>Message:</strong> %s</div>', $this->escapeHtml($message));
        }
        if (($file = $e->getFile())) {
            $html .= sprintf('<div><strong>File:</strong> %s</div>', $this->escapeHtml($this->replaceRoot($file)));
        }
        if (($line = $e->getLine())) {
            $html .= sprintf('<div><strong>Line:</strong> %s</div>', (int) $line);
        }

        $traces = $this->normalizeBacktraces($e->getTrace());
        if (! empty($traces)) {
            $html .= '<h2>Trace</h2>';

            $html .= '<table><tbody>';

            foreach ($traces as $index => $frame) {
                $html .= sprintf('<tr><td>#%d</td><td>%s</td><td>%s</td></tr>', count($traces) - $index, $this->escapeHtml($frame['function']), $this->escapeHtml($this->replaceRoot($frame['file'] ?: '')));
            }
            $html .= '</table></tbody>';
        }

        return $html;
    }

    /**
     * Get the code of the exception that is currently being handled.
     *
     * @return string
     */
    private function getExceptionCode(Throwable $exception): string
    {
        /*
        // TODO : utiliser plutot ce bout de code au lieu de faire un test sur l'instance HttpException
                if (method_exists($e, 'getStatusCode')) {
                    $code = $e->getStatusCode();
                } else {
                    $code = $e->getCode();
                }
        */
        $code = $exception->getCode();
        if ($exception instanceof HttpException) {
            // ErrorExceptions wrap the php-error types within the 'severity' property
            $code = $exception->getStatusCode();
        }
        if ($exception instanceof ErrorException) {
            // ErrorExceptions wrap the php-error types within the 'severity' property
            $code = $this->translateErrorCode($exception->getSeverity());
        }

        return (string) $code;
    }

    /**
     * Translate ErrorException code into the represented constant.
     *
     * @param int $errorCode
     *
     * @return string
     */
    private static function translateErrorCode(int $errorCode): string
    {
        $constants = get_defined_constants(true);
        if (array_key_exists('Core', $constants)) {
            foreach ($constants['Core'] as $constant => $value) {
                if (substr($constant, 0, 2) === 'E_' && $value === $errorCode) {
                    return $constant;
                }
            }
        }

        return 'E_UNKNOWN';
    }

    /**
     * normalizeBacktraces.
     *
     * @param array $traces
     *
     * @return array
     */
    protected function normalizeBacktraces(array $traces)
    {
        $return = [];
        foreach ($traces as $frame) {
            $return[] = $frame ? $this->normalizeBacktrace($frame) : null;
        }

        return $return;
    }

    /**
     * normalizeBacktrace.
     *
     * @param array $trace
     *
     * @return array
     */
    private function normalizeBacktrace(array $frame)
    {
        $arguments = '';
        if (isset($frame['args'])) {
            foreach ($frame['args'] as $arg) {
                $arguments .= (strlen($arguments) === 0) ? '' : ', ';
                if (is_object($arg)) {
                    $arguments .= get_class($arg);
                } elseif (is_string($arg)) {
                    $preparedArgument = (strlen($arg) < 100) ? $arg : substr($arg, 0, 50) . '…' . substr($arg, -50);
                    $arguments .= '"' . $preparedArgument . '"';
                } elseif (is_numeric($arg)) {
                    $arguments .= (string) $arg;
                } elseif (is_bool($arg)) {
                    $arguments .= ($arg === true ? 'TRUE' : 'FALSE');
                } elseif (is_array($arg)) {
                    $arguments .= 'array|' . count($arg) . '|';
                } else {
                    $arguments .= gettype($arg);
                }
            }
        }

        return [
            'file'     => $frame['file'] ? $frame['file'] . ' (' . $frame['line'] . ')' : null,
            'function' => ($frame['class'] ? $frame['class'] . $frame['type'] : null) . $frame['function'] . '(' . $arguments . ')',
        ];
    }

    /**
     * replaceRoot.
     *
     * @param string $file
     *
     * @return string
     */
    protected function replaceRoot(string $file): string
    {
        if (defined('Chiron\ROOT_DIR')) {
            $file = 'ROOT' . substr($file, strlen(\Chiron\ROOT_DIR));
        }

        return $file;
    }

    /**
     * HTML-encodes a string.
     */
    protected function escapeHtml(string $str): string
    {
        // TODO : attention on n'a pas stocké le charset dans cette classe, la variable $this->charset ne va pas marcher !!!!
        // TODO : récupérer le charset qui est stocké dans le container ?????
        return htmlspecialchars($str, ENT_COMPAT | ENT_SUBSTITUTE, 'UTF-8'); //$this->container->charset);
        //return htmlspecialchars($str, ENT_NOQUOTES, 'UTF-8');
    }
}
