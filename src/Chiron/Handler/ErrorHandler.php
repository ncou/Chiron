<?php

declare(strict_types=1);

namespace Chiron\Handler;

use Chiron\Handler\Formatter\FormatterInterface;
use Chiron\Handler\Formatter\PlainTextFormatter;
use Chiron\Handler\Reporter\ReporterInterface;
use Chiron\Http\Exception\HttpException;
//use Chiron\Http\Psr\Response;
use Exception;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

//https://github.com/narrowspark/framework/blob/ccda2dca0c312dbea08814d1372c1802920ebcca/src/Viserio/Component/Exception/ErrorHandler.php
//https://github.com/narrowspark/framework/blob/ccda2dca0c312dbea08814d1372c1802920ebcca/src/Viserio/Component/Exception/Http/Handler.php
//https://github.com/narrowspark/framework/blob/d56a37a9271908ac1ed039331bbf17a8913449f5/src/Viserio/Component/Exception/Console/Handler.php

//https://github.com/yiisoft/yii2/blob/master/framework/base/ErrorHandler.php

class ErrorHandler implements ErrorHandlerInterface
{
    /** ResponseFactoryInterface */
    private $responseFactory;

    /**
     * List of reporters used to report the exception data.
     *
     * @var \Chiron\Exception\Reporter\ReporterInterface[]
     */
    private $reporters = [];

    /**
     * List of formatters used to format the exception data.
     *
     * @var \Chiron\Exception\Formatter\FormatterInterface[]
     */
    private $formatters = [];

    /**
     * Default formatter to use in case all the filters fails.
     *
     * @var \Chiron\Exception\Formatter\FormatterInterface
     */
    private $defaultFormatter;

    /**
     * The formatter should be verbose (show stacktrace) only in debug mode.
     *
     * @var bool
     */
    private $shouldBeVerbose;

    /**
     * @var string
     */
    //private $contentType;
    /**
     * @var string
     */
    //private $method;
    /**
     * @var ServerRequestInterface
     */
    //private $request;

    /**
     * @var Throwable
     */
    private $exception;

    /**
     * A list of the exception types (classname) that should not be reported.
     *
     * @var string[]
     */
    // TODO : refléchir comment alimenter cette liste !!!!
    protected $dontReport = [];

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
        $this->defaultFormatter = new PlainTextFormatter();
    }

    /**
     * Determine if the exception should be reported.
     *
     * @param \Throwable $e
     *
     * @return bool
     */
    public function shouldReport(Throwable $e): bool
    {
        return ! $this->shouldntReport($e);
    }

    /**
     * Determine if the exception is in the do not report list.
     *
     * @param \Throwable $e
     *
     * @return bool
     */
    protected function shouldntReport(Throwable $e): bool
    {
        foreach ($this->dontReport as $class) {
            return $e instanceof $class;
        }

        return false;
    }

    /**
     * Add the reporter to the existing array of reporters.
     *
     * @param \Chiron\Exception\Reporter\ReporterInterface $reporter Reporter to use in this error handler
     */
    // TODO : permettre de passer un tableau à cette méthode.
    // TODO : créer une méthode pour faire un remove du reporter.
    public function addReporter(ReporterInterface $reporter): void
    {
        array_push($this->reporters, $reporter);
    }

    /**
     * Add the formatter to the existing array of formatters.
     *
     * @param \Chiron\Exception\Formatter\FormatterInterface $formatter Formatter to use in this error handler
     */
    // TODO : permettre de passer un tableau à cette méthode
    // TODO : créer une méthode pour faire un remove du formatter.
    public function addFormatter(FormatterInterface $formatter): void
    {
        array_push($this->formatters, $formatter);
    }

    /**
     * set a default formatter in case none of the formatters match the filters.
     *
     * @param \Chiron\Exception\Formatter\FormatterInterface $formatter Formatter to use in this error handler
     */
    // TODO : faire une méthode getDefaultFormatter qui initialisera un new TextPlainFormatter() si l'utilisateur n'a pas défini de formatteur par défaut !!!!
    public function setDefaultFormatter(FormatterInterface $formatter): void
    {
        $this->defaultFormatter = $formatter;
    }

    /**
     * Handle the exception and return PSR7 response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Throwable                               $e
     * @param bool                                     $displayErrorDetails
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    // TODO : mettre l'exception et la request directement dans des varaibles de la classe pour éviter de devoir à chaque fois les passer en paramétre aux fonctions. Ca permettra de déplacer ce bout de code directement dans la méthode createResponse()
    public function handle(ServerRequestInterface $request, Throwable $e, bool $displayErrorDetails): ResponseInterface
    {
        $this->shouldBeVerbose = $displayErrorDetails;

        $this->report($request, $e);

        $formatter = $this->getFilteredFormatter($e, $request);
        $body = $formatter->format($request, $e);

        $statusCode = $this->determineStatusCode($e, $request);
        $contentType = $formatter->contentType();

        $response = $this->createResponse($statusCode, $contentType, $body);

        if ($e instanceof HttpException) {
            $response = $this->injectHeaders($response, $e->getHeaders());
        }

        return $response;
    }

    /**
     * Execute all the reporters in the stack.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Throwable                               $e
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function report(ServerRequestInterface $request, Throwable $e): void
    {
        if ($this->shouldntReport($e)) {
            return;
        }

        foreach ($this->reporters as $reporter) {
            if ($reporter->canReport($e)) {
                $reporter->report($request, $e);
            }
        }
    }

    /**
     * Get the filtered formatter instance.
     *
     * @param \Throwable             $e
     * @param ServerRequestInterface $request
     *
     * @return \Chiron\Exception\Formatter\FormatterInterface
     */
    private function getFilteredFormatter(Throwable $e, ServerRequestInterface $request): FormatterInterface
    {
        $filtered = $this->formatters;

        foreach ($filtered as $index => $formatter) {
            // *** isVerbose Filter ***
            if (! $this->shouldBeVerbose) {
                if ($formatter->isVerbose()) {
                    unset($filtered[$index]);

                    continue;
                }
            }
            // *** CanFormat Filter ***
            if (! $formatter->canFormat($e)) {
                unset($filtered[$index]);

                continue;
            }
            // *** Content-Type Filter ***
            if (! $this->isAcceptableContentType($request, $formatter->contentType())) {
                unset($filtered[$index]);

                continue;
            }
        }

        // use a default formatter if there is none present after applying the filters. Else use the first one present in the array.
        // TODO : attention on devrait lever une exception si il n'y a pas de default formatter de défini par l'utilisateur, ou alors à minima on fait un rethrow de l'exception.
        return reset($filtered) ?: $this->defaultFormatter;
    }

    /**
     * Determines whether the current requests accepts a given content type.
     *
     * Note: This method is a bare-bones implementation designed specifically for
     * Chiron error handling requirements. Consider a fully-feature solution such
     * as willdurand/negotiation for any other situation.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param string                                   $contentType
     *
     * @return bool
     */
    // TODO : bout de code à déplacer dans une classe FormatNegociator ????
    private function isAcceptableContentType(ServerRequestInterface $request, string $contentType): bool
    {
        $acceptHeader = $request->getHeaderLine('Accept');

        if (strpos($acceptHeader, $contentType) !== false) {
            return true;
        }
        // special case for 'xxx+json' and 'xxx+xml' example : 'application/xhtml+xml'
        if (preg_match('/\+(json|xml)/', $acceptHeader, $matches)) {
            $mediaType = 'application/' . $matches[1];
            if ($mediaType === $contentType) {
                return true;
            }
        }
        // special case when accepting all subtypes of the mime 'mime/*'
        if (strpos($acceptHeader, strtok($contentType, '/') . '/*') !== false) {
            return true;
        }
        // special case when accepting all mimes '*/*'
        if (strpos($acceptHeader, '*/*') !== false) {
            return true;
        }

        return false;
    }

    /**
     * @param \Throwable                               $e
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return int
     */
    protected function determineStatusCode(Throwable $e, ServerRequestInterface $request): int
    {
        if ($request->getMethod() === 'OPTIONS') {
            return 200;
        }

        if ($e instanceof HttpException) {
            return $e->getStatusCode();
        }

        return 500;
    }

// TODO : virer cette méthode et utiliser celle qui existe dans le Kernel !!!!
    public function createResponse(int $statusCode, string $contentType, string $body): ResponseInterface
    {
        /*
                foreach (\array_merge($headers, ['Content-Type' => $this->getContentType()]) as $header => $value) {
                    $response = $response->withAddedHeader($header, $value);
                }
                $body = $response->getBody();
                $body->write(\json_encode(['errors' => [$error]], \JSON_HEX_TAG | \JSON_HEX_APOS | \JSON_HEX_AMP | \JSON_HEX_QUOT | \JSON_UNESCAPED_SLASHES));
                $body->rewind();
                return $response->withBody($body);
        */

        // TODO : attention il manque le choix de la version HTTP 1.1 ou 1.0 lorsqu'on initialise cette nouvelle response.
        $response = $this->responseFactory->createResponse($statusCode);

        // TODO : attention il manque le charset dans ce Content-Type !!!!!
        $response = $response->withHeader('Content-Type', $contentType);

        $response->getBody()->write($body);
        $response->getBody()->rewind();

        /*
        $body = $response->getBody();
        $body->write($bodyContent);
        $body->rewind();
        return $response->withBody($body);
*/

        return $response;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array                               $headers
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function injectHeaders(ResponseInterface $response, array $headers = []): ResponseInterface
    {
        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }

    /*
     * Inject the provided Content-Type, if none is already present.
     *
     * @return array Headers with injected Content-Type
     */
    //
    /*
    private function injectContentType(string $contentType, array $headers) : array
    {
        $hasContentType = array_reduce(array_keys($headers), function ($carry, $item) {
            return $carry ?: (strtolower($item) === 'content-type');
        }, false);
        if (! $hasContentType) {
            $headers['content-type'] = [$contentType];
        }
        return $headers;
    }*/

    /*
     * Create a plain text response.
     *
     * Produces a text response with a Content-Type of text/plain and a default
     * status of 200.
     *
     * @param string|StreamInterface $text String or stream for the message body.
     * @param int $status Integer status code for the response; 200 by default.
     * @param array $headers Array of headers to use at initialization.
     * @throws Exception\InvalidArgumentException if $text is neither a string or stream.
     */
    /*
    public function __construct($text, int $status = 200, array $headers = [])
    {
        parent::__construct(
            $this->createBody($text),
            $status,
            $this->injectContentType('text/plain; charset=utf-8', $headers)
        );
    }*/
}
