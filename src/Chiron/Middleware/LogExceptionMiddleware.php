<?php
declare(strict_types = 1);

namespace Chiron\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

// TODO ; ajouter une description du middleware + ajouter de la phpdoc en entête des fonctions de la classe

final class LogExceptionMiddleware implements MiddlewareInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (\ErrorException $e) {
            $this->handleErrorException($e);
            throw $e;
        } catch (\Throwable $e) {
            $this->logger->critical($this->formatException($e));
            throw $e;
        }
    }

    private function handleErrorException(\ErrorException $e)
    {
        switch ($e->getSeverity()) {
            case E_ERROR:
            case E_RECOVERABLE_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
            case E_PARSE:
                $this->logger->error($this->formatException($e));
                break;
            case E_WARNING:
            case E_USER_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
                $this->logger->warning($this->formatException($e));
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $this->logger->notice($this->formatException($e));
                break;
            case E_STRICT:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                $this->logger->info($this->formatException($e));
                break;
        }
    }

    /**
     * Create plain text response and return it as a string
     *
     * @param Throwable $e
     * @return string
     */
    private function formatException(\Throwable $e): string
    {
        return sprintf(
            "%s: %s in file %s on line %d%s\n",
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $this->getTraceAsString($e->getTrace())
        );
    }

    /**
     * @param array $traces
     * @return string
     */
    private function getTraceAsString(array $traces) : string
    {
        $trace = '';
        foreach ($traces as $index => $record) {
            $trace .= PHP_EOL;
            $trace .= sprintf(
                "    #%s %s%s%s() called at %s:%s\n",
                $index,
                $record['class'] ?? '',
                isset($record['class'], $record['function']) ? $record['type'] : '',
                $record['function'] ?? '',
                $record['file'] ?? 'unknown',
                $record['line'] ?? 0
            );
        }
        return $trace;
    }
}