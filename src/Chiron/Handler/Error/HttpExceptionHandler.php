<?php

declare(strict_types=1);

namespace Chiron\Handler\Error;

use Chiron\Handler\Error\Formatter\HtmlFormatter;
use Chiron\Handler\Error\Formatter\JsonFormatter;
use Chiron\Handler\Error\Formatter\PlainTextFormatter;
use Chiron\Handler\Error\Formatter\WhoopsFormatter;
use Chiron\Handler\Error\Formatter\XmlFormatter;
use Chiron\Http\Exception\HttpException;
use Chiron\Http\Psr\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use UnexpectedValueException;

// TODO : regarder ici pour gérer les formater pour les messages : https://github.com/userfrosting/UserFrosting/blob/master/app/sprinkles/core/src/Error/ExceptionHandlerManager.php

// TODO : utiliser des renderer : https://github.com/userfrosting/UserFrosting/tree/master/app/sprinkles/core/src/Error/Renderer
class HttpExceptionHandler implements RequestHandlerInterface
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
                $formatter = new JsonFormatter();

                break;
            case 'text/xml':
            case 'application/xml':
                $formatter = new XmlFormatter();

                break;
            case 'text/plain':
                $formatter = new PlainTextFormatter();

                break;
            case 'text/html':
                $formatter = new HtmlFormatter();
                //$formatter = new WhoopsFormatter();

                break;
                // TODO : gérer le cas : 'text/plain' avec la même chose que le html mais sans les balises html...
            default:
                // TODO : If an Accept header field is present, and if the server cannot send a response which is acceptable according to the combined Accept field value, then the server SHOULD return a 406 (not acceptable) response.
                //https://github.com/phapi/middleware-content-negotiation/blob/master/src/Phapi/Middleware/ContentNegotiation/FormatNegotiation.php#L83
                // TODO : lever plutot une exception du genre http error 406 Not acceptable
                // TODO : réfléchir à ce cas car cela ne peut pas arriver car si le contentType n'est pas dans la liste définie en constante de classe on renvoit par défaut text/html !!!!!!!!!!
                throw new UnexpectedValueException('Cannot render unknown content type ' . $contentType);
        }

        return $formatter->formatException($exception, $displayErrorDetails);
    }

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

    /**
     * Returns the preferred format based on the Accept header.
     *
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    //https://github.com/franzliedke/whoops-middleware/blob/master/src/FormatNegotiator.php#L28
    /*
    public static function getPreferredFormat(ServerRequestInterface $request)
    {
        $acceptTypes = $request->getHeader('accept');
        if (count($acceptTypes) > 0) {
            $acceptType = $acceptTypes[0];
            // As many formats may match for a given Accept header, let's try to find the one that fits the best
            $counters = [];
            foreach (self::$formats as $format => $values) {
                foreach ($values as $value) {
                    $counters[$format] = isset($counters[$format]) ? $counters[$format] : 0;
                    $counters[$format] += intval(strpos($acceptType, $value) !== false);
                }
            }
            // Sort the array to retrieve the format that best matches the Accept header
            asort($counters);
            end($counters);
            return key($counters);
        }
        return 'html';
    }*/

    /**
     * Determine which content type we know about is wanted using Accept header.
     *
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    /*
    private function determineContentType(ServerRequestInterface $request)
    {
        $acceptHeader = $request->getHeaderLine('Accept');
        $selectedContentTypes = array_intersect(explode(',', $acceptHeader), $this->knownContentTypes);
        if (count($selectedContentTypes)) {
            return reset($selectedContentTypes);
        }
        return 'text/html';
    }*/

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return string
     */
    /*
    protected function getAcceptType(ServerRequestInterface $request)
    {
        $accept = $request->getHeaderLine('Accept');
        if (
            strpos($accept, 'text/html') !== false ||
            strpos($accept, 'application/xhtml+xml') !== false
        ) {
            return 'html';
        }
        if (
            strpos($accept, 'application/json') !== false ||
            strpos($accept, 'text/json') !== false ||
            strpos($accept, 'application/x-json') !== false
        ) {
            return 'json';
        }
        return 'text';
    }*/

    protected function shouldDisplayDetails(ServerRequestInterface $request): bool
    {
        return $request->getAttribute($this->attributeName . '_displayErrorDetails', false);
    }

    // TODO : attention il manque le choix de la version HTTP 1.1 ou 1.0 lorsqu'on initialise cette nouvelle response.
    protected function createResponseFromException(Throwable $e): ResponseInterface
    {
        // TODO : lui passer plutot une factory en paramétre comme ca on évite de rendre cette classe adhérente à la classe "Chiron\Http\Response"
        $response = new Response(500);

        if ($e instanceof HttpException) {
            // add the headers stored in the exception
            $headers = $e->getHeaders();
            foreach ($headers as $header => $value) {
                $response = $response->withAddedHeader($header, $value);
            }
            $response = $response->withStatus($e->getStatusCode());
        }

        return $response;
    }
}
