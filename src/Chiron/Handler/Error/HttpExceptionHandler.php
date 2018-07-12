<?php

declare(strict_types=1);

namespace Chiron\Handler\Error;

use Chiron\Http\Exception\HttpException;
use ErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use UnexpectedValueException;

// TODO : utiliser des renderer : https://github.com/userfrosting/UserFrosting/tree/master/app/sprinkles/core/src/Error/Renderer
class HttpExceptionHandler extends AbstractHandler
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /*
                $this->statusCode = $this->determineStatusCode();
                $this->contentType = $this->determineContentType($request);
                $this->renderer = $this->determineRenderer();
                if (!$this->displayErrorDetails) {
                    $this->writeToErrorLog();
                }
                return $this->formatResponse();
        */
        $exception = $this->retrieveException($request);

        // convert Throwable to HttpException
        /*
        if (! $this->isHttpException($exception)) {
            $exception = new HttpException(500, 'An unexpected error has occurred', $exception);
        }*/

        $contentType = $this->determineContentType($request);

        $displayErrorDetails = $this->shouldDisplayDetails($request);
        $body = $this->formatException($exception, $contentType, $displayErrorDetails);

        $response = $this->createResponseFromException($exception);

        // TODO : attention il manque le charset dans ce Content-Type !!!!!
        return $response->withHeader('Content-type', $contentType)->write($body);
    }

    private function formatException(Throwable $exception, string $contentType, bool $displayErrorDetails): string
    {
        switch ($contentType) {
            case 'application/json':
                $body = $this->renderJsonBody($exception);

                break;
            case 'text/xml':
            case 'application/xml':
                $body = $this->renderXmlBody($exception);

                break;
            case 'text/html':
                $body = $this->renderHtmlBody($exception, $displayErrorDetails);

                break;
                // TODO : gérer le cas : 'text/plain' avec la même chose que le html mais sans les balises html...
            default:
                // TODO : If an Accept header field is present, and if the server cannot send a response which is acceptable according to the combined Accept field value, then the server SHOULD return a 406 (not acceptable) response.
                //https://github.com/phapi/middleware-content-negotiation/blob/master/src/Phapi/Middleware/ContentNegotiation/FormatNegotiation.php#L83
                throw new UnexpectedValueException('Cannot render unknown content type ' . $contentType);
        }

        return $body;
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
     * Render JSON error.
     *
     * @param Throwable $error
     *
     * @return string
     */
    private function renderJsonBody(Throwable $error): string
    {
        $json = [
            'message' => 'Chiron Application Error',
        ];
//        if ($this->displayErrorDetails) {
        $json['error'] = [];
        do {
            $json['error'][] = [
                'type'    => get_class($error),
                'code'    => $error->getCode(),
                'message' => $error->getMessage(),
                'file'    => $this->replaceRoot($error->getFile()),
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
     * @param Throwable $error
     *
     * @return string
     */
    private function renderXmlBody(Throwable $error): string
    {
        $xml = "<?xml version='1.0' encoding='UTF-8'?>\n";
        $xml .= "<errors>\n  <message>Chiron Application Error</message>\n";
//        if ($this->displayErrorDetails) {
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
                if (substr($constant, 0, 2) == 'E_' && $value == $errorCode) {
                    return $constant;
                }
            }
        }

        return 'E_UNKNOWN';
    }
}
