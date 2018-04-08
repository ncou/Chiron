<?php
declare(strict_types = 1);

namespace Chiron\ErrorHandler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Psr\Http\Server\RequestHandlerInterface;

use Chiron\Http\Response;

use Chiron\Exception\HttpException;
use Throwable;
use ErrorException;

// TODO : utiliser des renderer : https://github.com/userfrosting/UserFrosting/tree/master/app/sprinkles/core/src/Error/Renderer


class HttpExceptionHandler extends AbstractExceptionHandler
{

    /**
     * Execute the error handler.
     */
    /*
        public function handle(ServerRequestInterface $request): ResponseInterface
        {
            $error = $request->getAttribute('error');
            $accept = $request->getHeaderLine('Accept');

            $response = new Response($error->getStatusCode());

            $headers = $error->getHeaders();
            foreach ($headers as $header => $value) {
                $response = $response->withAddedHeader($header, $value);
            }

            foreach ($this->handlers as $method => $types) {
                foreach ($types as $type) {
                    if (stripos($accept, $type) !== false) {
                        $response->getBody()->write(call_user_func(__CLASS__.'::'.$method, $error));

                        return $response->withHeader('Content-Type', $type);
                    }
                }
            }

            //$response->getBody()->write(static::html($error));
            $response->write(static::html($error));

            return $response->withHeader('Content-Type', 'text/html');
        }
    */

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
        $body = $this->formatException($exception, $contentType);

        $response = $this->createResponseFromException($exception);

        //return new \Chiron\Http\Response();

        return $response->withHeader('Content-type', $contentType)->write($body);
    }

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
                break;
            default:
                throw new UnexpectedValueException('Cannot render unknown content type ' . $contentType);
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








    /**
     * Render HTML error page
     *
     * @param Chiron\Exception\HttpException $error
     *
     * @return string
     */
    private function renderHtmlErrorMessage(Throwable $error)
    {

        /*
        switch ($error->getStatusCode()) {
            case 404:
                $title = 'Sorry, the page you are looking for could not be found.';
                break;
            default:
                $title = 'Whoops, looks like something went wrong.';
        }*/

        $title = 'Chiron Error';
//        if ($this->displayErrorDetails) {
        $html = '<h1>' . $title . '</h1>';
        $html .= '<p class="lead">Whoops, looks like something went wrong.</p>';
        $html .= '<h2>&bull; Error Details</h2>';
        $html .= $this->renderHtmlError($error);
        while ($error = $error->getPrevious()) {
            $html .= '<h2>&bull; Previous Error</h2>';
            $html .= $this->renderHtmlError($error);
        }
//        } else {
//            $html = '<p>A website error has occurred. Sorry for the temporary inconvenience.</p>';
//        }
        $output = "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'><title>{$title}</title><style>body{margin:0;padding:20px;font-family:Helvetica,Arial,Verdana,sans-serif;font-size:15px;line-height:150%}h1{margin:0;font-size:40px;font-weight:normal;line-height:40px;padding-bottom: 10px;border-bottom:1px solid #eee}p.lead{font-size:22px}strong{display:inline-block;width:85px}table{border-spacing:0;border-collapse:collapse;width:100%}table tbody tr td{padding:8px;line-height:1.42857143;vertical-align:top;border-top:1px solid #ddd;font-family:monospace}table>tbody>tr:nth-child(odd)>td{background-color:#f9f9f9}</style></head><body>{$html}</body></html>";

        return $output;
    }
    /**
     * Render error as HTML.
     *
     * @param Throwable $error
     *
     * @return string
     */
    private function renderHtmlError(Throwable $error): string
    {
        $html = sprintf('<div><strong>Type:</strong> %s (%s)</div>', get_class($error), $this->getExceptionCode($error));

        //if (($code = $error->getCode())) {
        //    $html .= sprintf('<div><strong>Code:</strong> %s</div>', $code);
        //}
        if (($message = $error->getMessage())) {
            $html .= sprintf('<div><strong>Message:</strong> %s</div>', $this->escapeHtml($message));
        }
        if (($file = $error->getFile())) {
            $html .= sprintf('<div><strong>File:</strong> %s</div>', $this->escapeHtml($this->replaceRoot($file)));
        }
        if (($line = $error->getLine())) {
            $html .= sprintf('<div><strong>Line:</strong> %s</div>', (int)$line);
        }
    
        $traces = $this->normalizeBacktraces($error->getTrace());
        if (! empty($traces)) {
            $html .= '<h2>Trace</h2>';
            
            $html .= '<table><tbody>';
            foreach ($traces as $index => $trace) {
                $html .= sprintf('<tr><td>#%d</td><td>%s</td><td>%s</td></tr>', count($traces) - $index, $this->escapeHtml($trace['function']), $this->escapeHtml($trace['file']));
            }
            $html .= '</table></tbody>';
        }

        return $html;
    }

    /**
     * Render JSON error
     *
     * @param Throwable $error
     *
     * @return string
     */
    private function renderJsonErrorMessage(Throwable $error): string
    {
        $json = [
            'message' => 'Chiron Application Error',
        ];
//        if ($this->displayErrorDetails) {
        $json['error'] = [];
        do {
            $json['error'][] = [
                    'type' => get_class($error),
                    'code' => $error->getCode(),
                    'message' => $error->getMessage(),
                    'file' => $this->replaceRoot($error->getFile()),
                    'line' => $error->getLine(),
                    'trace' => explode("\n", $error->getTraceAsString())
                ];
        } while ($error = $error->getPrevious());
//        }
        return json_encode($json, JSON_PRETTY_PRINT); //JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    }
    /**
     * Render XML error
     *
     * @param Throwable $error
     *
     * @return string
     */
    private function renderXmlErrorMessage(Throwable $error): string
    {
        $xml = "<?xml version='1.0' encoding='UTF-8'?>\n";
        $xml .= "<errors>\n  <message>Chiron Application Error</message>\n";
//        if ($this->displayErrorDetails) {
        do {
            $xml .= "  <error>\n";
            $xml .= "    <type>" . get_class($error) . "</type>\n";
            $xml .= "    <code>" . $error->getCode() . "</code>\n";
            $xml .= "    <message>" . $this->createCdataSection($error->getMessage()) . "</message>\n";
            $xml .= "    <file>" . $error->getFile() . "</file>\n";
            $xml .= "    <line>" . $error->getLine() . "</line>\n";
            $xml .= "    <trace>" . $this->createCdataSection($error->getTraceAsString()) . "</trace>\n";
            $xml .= "  </error>\n";
        } while ($error = $error->getPrevious());
//        }
        $xml .= "</errors>";
        return $xml;
    }
    /**
     * Returns a CDATA section with the given content.
     *
     * @param  string $content
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
     * @param int $error_code
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
        return "E_UNKNOWN";
    }

    // TODO : attention il manque le choix de la version HTTP 1.1 ou 1.0 lorsqu'on initialise cette nouvelle response. Idem pour le charset par défaut défini dans l'application !!!!
    private function createResponseFromException(Throwable $e): ResponseInterface
    {
        // TODO : lui passer plutot une factory en paramétre comme ca on évite de rendre cette classe adhérente à la classe "Chiron\Http\Response"
        $response = new Response();

        // determine the status code to use for the response
        // TODO : en fait on devrait toujours avoir une HTTPException, donc on peut récupérer directement le getStatusCode et pas forcément le code 500 !!!!! Modifier aussi le type du paramétre.
        $statusCode = $this->isHttpException($e) ? $e->getStatusCode() : 500;

        // add the headers stored in the exception
        // TODO : en fait on devrait toujours avoir une HTTPException, donc on peut récupérer directement le getStatusCode et pas forcément le code 500 !!!!! Modifier aussi le type du paramétre.
        $headers = $this->isHttpException($e) ? $e->getHeaders() : [];
        foreach ($headers as $header => $value) {
            $response = $response->withAddedHeader($header, $value);
        }
        
        return $response->withStatus($statusCode);
    }

    // TODO : en fait on devrait toujours avoir une HTTPException dans la request, donc cette méthode ne sert à rien !!!!
    private function isHttpException(Throwable $e): bool
    {
        return ($e instanceof HttpException);
    }
}
