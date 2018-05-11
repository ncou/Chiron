<?php

declare(strict_types=1);

namespace Chiron\Handler\Error;

// TODO : regarder ici pour gérer les formater pour les messages : https://github.com/userfrosting/UserFrosting/blob/master/app/sprinkles/core/src/Error/ExceptionHandlerManager.php

use Chiron\Exception\HttpException;
use Chiron\Http\Response;
use Psr\Http\Message\ResponseInterface;
//use Psr\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use UnexpectedValueException;

// TODO : il faudra surement ajouter une méthode setContainer() à cette classe + ajouter le "use Psr\Container\ContainerInterface;"
abstract class AbstractHandler implements RequestHandlerInterface
{
    /**
     * The request attribute name used to retrieve the exception stored previously (in the middleware).
     *
     * @var string
     */
    protected $attributeName = 'Chiron:exception';

    /**
     * Known handled content types.
     *
     * @var array
     */
    protected $knownContentTypes = [
        'application/json',
        'application/xml',
        'text/xml',
        'text/html',
        'text/plain',
    ];

    /**
     * Dependency injection container.
     *
     * @var ContainerInterface
     */
    //protected $container;

    /**
     * Set container.
     *
     * @param ContainerInterface $container
     */
    /*
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }*/

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

    abstract public function handle(ServerRequestInterface $request): ResponseInterface;

    protected function retrieveException(ServerRequestInterface $request): Throwable
    {
        //retrieve the "HttpException object" stored in the request attribute
        $exception = $request->getAttribute($this->attributeName);

        if (! $exception instanceof Throwable) {
            throw new UnexpectedValueException(
                sprintf(
                    'No valid exception provided (%s) for the request attribute [%s]. It must return an instance of (\Throwable)',
                    is_object($exception) ? get_class($exception) : gettype($exception),
                    $this->attributeName
                )
            );
        }

        return $exception;
    }

    /**
     * Determine which content type we know about is wanted using Accept header.
     *
     * Note: This method is a bare-bones implementation designed specifically for
     * Slim's error handling requirements. Consider a fully-feature solution such
     * as willdurand/negotiation for any other situation.
     *
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    // TODO : autre exemple : https://github.com/userfrosting/UserFrosting/blob/master/app/sprinkles/core/src/Http/Concerns/DeterminesContentType.php#L42
    // TODO : autre example : https://github.com/franzliedke/whoops-middleware/blob/master/src/FormatNegotiator.php#L28
    protected function determineContentType(ServerRequestInterface $request): string
    {
        $acceptHeader = $request->getHeaderLine('Accept');
        $selectedContentTypes = array_intersect(explode(',', $acceptHeader), $this->knownContentTypes);
        $count = count($selectedContentTypes);
        if ($count) {
            $current = current($selectedContentTypes);
            /*
             * Ensure other supported content types take precedence over text/plain
             * when multiple content types are provided via Accept header.
             */
            if ($current === 'text/plain' && $count > 1) {
                return next($selectedContentTypes);
            }

            return $current;
        }
        if (preg_match('/\+(json|xml)/', $acceptHeader, $matches)) {
            $mediaType = 'application/' . $matches[1];
            if (in_array($mediaType, $this->knownContentTypes)) {
                return $mediaType;
            }
        }

        return 'text/html';
    }

    protected function shouldDisplayDetails(ServerRequestInterface $request): bool
    {
        return $request->getAttribute($this->attributeName . '_displayErrorDetails', false);
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

    // TODO : attention il manque le choix de la version HTTP 1.1 ou 1.0 lorsqu'on initialise cette nouvelle response. Idem pour le charset par défaut défini dans l'application !!!!
    protected function createResponseFromException(Throwable $e): ResponseInterface
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

    protected function isHttpException(Throwable $e): bool
    {
        return $e instanceof HttpException;
    }
}
