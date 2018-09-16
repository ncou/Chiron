<?php

declare(strict_types=1);

namespace Chiron\Handler\Error\Formatter;

use Chiron\Http\Exception\HttpException;
use ErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use UnexpectedValueException;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\VarDumper\Cloner\AbstractCloner;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Whoops\Exception\Formatter;
use Whoops\Exception\Inspector;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\HandlerInterface;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\XmlResponseHandler;
use Whoops\Run as Whoops;
use Whoops\Util\Misc;
use Whoops\Util\TemplateHelper;

// ajouter les informations sur la request de l'application !!!!
//https://github.com/zendframework/zend-expressive/blob/master/src/Middleware/WhoopsErrorResponseGenerator.php#L95

//https://github.com/userfrosting/UserFrosting/blob/master/app/sprinkles/core/src/Error/Renderer/WhoopsRenderer.php
//https://github.com/filp/whoops/blob/master/src/Whoops/Handler/PrettyPageHandler.php

//https://github.com/zeuxisoo/php-slim-whoops/blob/master/src/Zeuxisoo/Whoops/Provider/Slim/WhoopsGuard.php#L47

class WhoopsFormatter implements ExceptionFormatterInterface
{

    public function format(Throwable $e): string
    {
        return $this->whoops()->handleException($e);
    }

    /**
     * Get the whoops instance.
     *
     * @return \Whoops\Run
     */
    private function whoops()
    {
        $whoops = new Whoops();
        $whoops->allowQuit(false);
        $whoops->writeToOutput(false);
        $whoops->pushHandler(new PrettyPageHandler());
        return $whoops;
    }

    /**
     * Get the supported content type.
     *
     * @return string
     */
    public function contentType(): string
    {
        return 'text/html';
    }

    /**
     * Do we provide verbose information about the exception?
     *
     * @return bool
     */
    public function isVerbose(): bool
    {
        return true;
    }

    /**
     * Can we format the exception?
     *
     * @param \Exception $e
     *
     * @return bool
     */
    public function canFormat(Throwable $e): bool
    {
        return class_exists(Whoops::class);
    }
}
