<?php

declare(strict_types=1);

//https://github.com/slimphp/Slim/blob/3.x/Slim/Handlers/NotAllowed.php

namespace Chiron\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class NotAllowedHandler extends AbstractExceptionHandler
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $exception = $this->retrieveException($request);

        $contentType = $this->determineContentType($request);
        $body = $this->formatException($exception, $contentType);

        $response = $this->createResponseFromException($exception);

        return $response->withHeader('Content-type', $contentType)->write($body);
    }

    // TODO : le type de l'exception doit etre "HttpException" et non pas throwable !!!!
    private function formatException(Throwable $exception, string $contentType): string
    {
        switch ($contentType) {
            case 'application/json':
                $body = $this->renderJsonErrorMessage($exception);
                break;
            case 'text/xml':
            case 'application/xml':
                $body = $this->renderXmlErrorMessage($exception);
                break;
            case 'text/html':
                $body = $this->renderHtmlErrorMessage($exception);
                //$output = $this->format($error);
                break;
            default:
                throw new UnexpectedValueException('Cannot render unknown content type '.$contentType);
        }

//        $this->writeToErrorLog($error);

        return $body;
    }

    /**
     * Return the error as html.
     */
    /*
    public static function html(HttpException $error): string
    {
        return <<<EOT
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Error {$error->getStatusCode()}</title>
    <style>html{font-family: sans-serif;}</style>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <h1>Error {$error->getStatusCode()}</h1>
    {$error->getMessage()}
</body>
</html>
EOT;
    }*/

    /**
     * Return the error as json.
     */
    /*
    public static function json(HttpException $error): string
    {
        return json_encode([
            'error' => [
                'code' => $error->getStatusCode(),
                'message' => $error->getMessage(),
            ],
        ]);
    }*/

    /**
     * Return the error as xml.
     */
    /*
    public static function xml(HttpException $error): string
    {
        return <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<error>
    <code>{$error->getStatusCode()}</code>
    <message>{$error->getMessage()}</message>
</error>
EOT;
    }*/

    private function format(Throwable $exception) : string
    {
        $html = '<h2>An error has occurred</h2>';
        $html .= "\n";
        $html .= $this->doFormat($exception);

        return $html;
    }

    private function doFormat(Throwable $exception)
    {
        $html = '<p>'.$exception->getMessage().'</p>';
//        if ($this->options['includeTrace']) {
        $html .= "\n";
        $html .= '<strong>Trace:</strong><br>';
        $html .= "\n";
        $trace = $exception->getTrace();
        $traceLength = count($trace);
        foreach ($exception->getTrace() as $i => $traceRecord) {
            $html .= "\n";
            $html .= $this->formatTraceRecord($traceRecord, $i, $traceLength).'<br>';
        }
//        }
        if ($previous = $exception->getPrevious()) {
            $html .= "\n";
            $html .= '<br><strong>Previous:</strong><br>';
            $html .= "\n";
            $html .= $this->doFormat($previous);
        }

        return $html;
    }

    private function formatTraceRecord(array $traceRecord, int $index, int $traceLength) : string
    {
        return sprintf(
            '#%s %s%s%s in %s:%s',
            $traceLength - $index - 1,
            $traceRecord['class'] ?? '',
            isset($traceRecord['class'], $traceRecord['function']) ? ':' : '',
            $traceRecord['function'] ?? '',
            $traceRecord['file'] ?? 'unknown',
            $traceRecord['line'] ?? 0
        );
    }

    /**
     * Return the error as plain text.
     */
    public static function plain(HttpException $error): string
    {
        return sprintf("Error %s\n%s", $error->getStatusCode(), $error->getMessage());
    }

    /**
     * Render HTML error page.
     *
     * @param \Throwable $error
     *
     * @return string
     */
    private function renderHtmlErrorMessage(\Throwable $error)
    {
        /*
        switch ($error->getStatusCode()) {
            case 404:
                $title = 'Sorry, the page you are looking for could not be found.';
                break;
            default:
                $title = 'Whoops, looks like something went wrong.';
        }*/

        $title = 'Nano Application Error';
//        if ($this->displayErrorDetails) {
        $html = '<p>The application could not run because of the following error:</p>';
        $html .= '<h2>Details</h2>';
        $html .= $this->renderHtmlError($error);
        while ($error = $error->getPrevious()) {
            $html .= '<h2>Previous error</h2>';
            $html .= $this->renderHtmlError($error);
        }
//        } else {
//            $html = '<p>A website error has occurred. Sorry for the temporary inconvenience.</p>';
//        }
        $output = sprintf(
            "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>".
            '<title>%s</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,'.
            'sans-serif;}h1{margin:0;font-size:40px;font-weight:normal;line-height:40px;}strong{'.
            'display:inline-block;width:65px;}</style></head><body><h1>%s</h1>%s</body></html>',
            $title,
            $title,
            $html
        );

        return $output;
    }

    /**
     * Render error as HTML.
     *
     * @param \Throwable $error
     *
     * @return string
     */
    private function renderHtmlError(\Throwable $error): string
    {
        $html = sprintf('<div><strong>Type:</strong> %s (%s)</div>', get_class($error), $this->getExceptionCode($error));

        //if (($code = $error->getCode())) {
        //    $html .= sprintf('<div><strong>Code:</strong> %s</div>', $code);
        //}
        if (($message = $error->getMessage())) {
            $html .= sprintf('<div><strong>Message:</strong> %s</div>', $this->escapeHtml($message));
        }
        if (($file = $error->getFile())) {
            $html .= sprintf('<div><strong>File:</strong> %s</div>', $this->escapeHtml($file));
        }
        if (($line = $error->getLine())) {
            $html .= sprintf('<div><strong>Line:</strong> %s</div>', (int) $line);
        }
        //if (($trace = $error->getTraceAsString())) {
        if (($trace = $this->getTraceAsString($error->getTrace()))) {
            $html .= '<h2>Trace</h2>';
            $html .= sprintf('<pre>%s</pre>', $this->escapeHtml($trace));
        }

        return $html;
    }

    private function getTraceAsString(array $trace): string
    {
        $backtraceCode = '';
        foreach ($trace as $index => $step) {
            $class = isset($step['class']) ? $step['class'].$step['type'] : '';
            $arguments = '';
            if (isset($step['args']) && is_array($step['args'])) {
                foreach ($step['args'] as $argument) {
                    $arguments .= (strlen($arguments) === 0) ? '' : ', ';
                    if (is_object($argument)) {
                        $arguments .= get_class($argument);
                    } elseif (is_string($argument)) {
                        $preparedArgument = (strlen($argument) < 100) ? $argument : substr($argument, 0, 50).'…'.substr($argument, -50);
                        $arguments .= '"'.$preparedArgument.'"';
                    } elseif (is_numeric($argument)) {
                        $arguments .= (string) $argument;
                    } elseif (is_bool($argument)) {
                        $arguments .= ($argument === true ? 'TRUE' : 'FALSE');
                    } elseif (is_array($argument)) {
                        $arguments .= 'array|'.count($argument).'|';
                    } else {
                        $arguments .= gettype($argument);
                    }
                }
            }
            $backtraceCode .= (count($trace) - $index).' '.$class.$step['function'].'('.$arguments.')'.PHP_EOL;
            $backtraceCode .= '     '.$step['file'].'('.$step['line'].')'.PHP_EOL;
        }

        return $backtraceCode;
    }

    /**
     * Render JSON error.
     *
     * @param \Throwable $error
     *
     * @return string
     */
    private function renderJsonErrorMessage(\Throwable $error): string
    {
        $json = [
            'message' => 'Slim Application Error',
        ];
//        if ($this->displayErrorDetails) {
        $json['error'] = [];
        do {
            $json['error'][] = [
                    'type'    => get_class($error),
                    'code'    => $error->getCode(),
                    'message' => $error->getMessage(),
                    'file'    => $error->getFile(),
                    'line'    => $error->getLine(),
                    'trace'   => explode("\n", $error->getTraceAsString()),
                ];
        } while ($error = $error->getPrevious());
//        }
        return json_encode($json, JSON_PRETTY_PRINT); //JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    }

    /**
     * Render XML error.
     *
     * @param \Throwable $error
     *
     * @return string
     */
    private function renderXmlErrorMessage(\Throwable $error): string
    {
        $xml = "<?xml version='1.0' encoding='UTF-8'?>\n";
        $xml .= "<errors>\n  <message>Slim Application Error</message>\n";
//        if ($this->displayErrorDetails) {
        do {
            $xml .= "  <error>\n";
            $xml .= '    <type>'.get_class($error)."</type>\n";
            $xml .= '    <code>'.$error->getCode()."</code>\n";
            $xml .= '    <message>'.$this->createCdataSection($error->getMessage())."</message>\n";
            $xml .= '    <file>'.$error->getFile()."</file>\n";
            $xml .= '    <line>'.$error->getLine()."</line>\n";
            $xml .= '    <trace>'.$this->createCdataSection($error->getTraceAsString())."</trace>\n";
            $xml .= "  </error>\n";
        } while ($error = $error->getPrevious());
//        }
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

    /**
     * Get the code of the exception that is currently being handled.
     *
     * @return string
     */
    private function getExceptionCode(\Throwable $exception): string
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
        if ($exception instanceof \HttpException) {
            // ErrorExceptions wrap the php-error types within the 'severity' property
            $code = $exception->getStatusCode();
        }
        if ($exception instanceof \ErrorException) {
            // ErrorExceptions wrap the php-error types within the 'severity' property
            $code = $this->translateErrorCode($exception->getSeverity());
        }

        return (string) $code;
    }

    /**
     * Translate ErrorException code into the represented constant.
     *
     * @param int $error_code
     *
     * @return string
     */
    private static function translateErrorCode($error_code): string
    {
        $constants = get_defined_constants(true);
        if (array_key_exists('Core', $constants)) {
            foreach ($constants['Core'] as $constant => $value) {
                if (substr($constant, 0, 2) == 'E_' && $value == $error_code) {
                    return $constant;
                }
            }
        }

        return 'E_UNKNOWN';
    }
}
