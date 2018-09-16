<?php

declare(strict_types=1);

namespace Chiron\Handler\Error;

use Chiron\Handler\Error\ExceptionInfo;
use Chiron\Handler\Error\ExceptionHandler;
use Chiron\Handler\Error\Formatter\HtmlFormatter;
use Chiron\Handler\Error\Formatter\JsonFormatter;
use Chiron\Handler\Error\Formatter\ViewFormatter;
use Chiron\Handler\Error\Formatter\PlainTextFormatter;
use Chiron\Handler\Error\Formatter\WhoopsFormatter;
use Chiron\Handler\Error\Formatter\TemplateHtmlFormatter;
use Chiron\Handler\Error\Formatter\XmlFormatter;
use Chiron\Http\Exception\HttpExceptionInterface;
use Chiron\Http\Psr\Response;
use Chiron\Http\Psr\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use Exception;
use RuntimeException;
use UnexpectedValueException;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Chiron\Handler\Error\Formatter\ExceptionFormatterInterface;

class HttpExceptionHandler extends ExceptionHandler
{
    public function render(Throwable $e, ServerRequestInterface $request): ResponseInterface
    {
        $response = parent::render($e, $request);

        $headers = $e->getHeaders();
        foreach ($headers as $header => $value) {
            $response = $response->withHeader($header, $value);
        }

        return $response->withStatus($e->getStatusCode());
    }
}
