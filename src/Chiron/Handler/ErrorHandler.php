<?php

declare(strict_types=1);

namespace Chiron\Handler;

use Chiron\Handler\Formatter\FormatterInterface;
use Chiron\Handler\Reporter\ReporterInterface;
use Chiron\Http\Exception\HttpException;
use Chiron\Http\Psr\Response;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

//https://github.com/narrowspark/framework/blob/ccda2dca0c312dbea08814d1372c1802920ebcca/src/Viserio/Component/Exception/ErrorHandler.php
//https://github.com/narrowspark/framework/blob/ccda2dca0c312dbea08814d1372c1802920ebcca/src/Viserio/Component/Exception/Http/Handler.php
//https://github.com/narrowspark/framework/blob/d56a37a9271908ac1ed039331bbf17a8913449f5/src/Viserio/Component/Exception/Console/Handler.php

//https://github.com/yiisoft/yii2/blob/master/framework/base/ErrorHandler.php

class ErrorHandler implements HandlerInterface
{
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
     * @param bool $shouldBeVerbose
     */
    public function __construct(bool $shouldBeVerbose)
    {
        $this->shouldBeVerbose = $shouldBeVerbose;
    }

    /**
     * Report an exception.
     *
     * @param \Throwable                               $e
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    // TODO : vérifier si le $request est utilisé et nécessaire
    public function report(Throwable $e, ServerRequestInterface $request): void
    {
        foreach ($this->reporters as $reporter) {
            if ($reporter->canReport($e)) {
                $reporter->report($e);
            }
        }
    }

    public function render(Throwable $e, ServerRequestInterface $request): ResponseInterface
    {
        $formatter = $this->getFilteredFormatter($e, $request);

        $content = $formatter->format($e);

        // TODO : attention il manque le choix de la version HTTP 1.1 ou 1.0 lorsqu'on initialise cette nouvelle response.
        // TODO : passer une ResponseFactory dans le constructeur de cette classe et utiliser la factory
        $statusCode = $this->determineStatusCode($e, $request);
        $response = new Response($statusCode);

        // TODO : attention il manque le charset dans ce Content-Type !!!!!
        $response = $response->withHeader('Content-Type', $formatter->contentType());

        $response->getBody()->write($content);

        if ($e instanceof HttpException) {
            $response = $this->injectHeaders($response, $e->getHeaders());
        }

        return $response;
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

    /**
     * Get the formatter instance.
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
        return reset($filtered) ?? $this->defaultFormatter;
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
     * Add the reporter to the existing array of reporters.
     *
     * @param \Chiron\Exception\Reporter\ReporterInterface $reporter Reporter to use in this error handler
     */
    public function addReporter(ReporterInterface $reporter): void
    {
        array_push($this->reporters, $reporter);
    }

    /**
     * Add the formatter to the existing array of formatters.
     *
     * @param \Chiron\Exception\Formatter\FormatterInterface $formatter Formatter to use in this error handler
     */
    public function addFormatter(FormatterInterface $formatter): void
    {
        array_push($this->formatters, $formatter);
    }

    /**
     * set a default formatter in case none of the formatters match the filters.
     *
     * @param \Chiron\Exception\Formatter\FormatterInterface $formatter Formatter to use in this error handler
     */
    public function setDefaultFormatter(FormatterInterface $formatter): void
    {
        $this->defaultFormatter = $formatter;
    }
}
