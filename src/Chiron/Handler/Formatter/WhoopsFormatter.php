<?php

declare(strict_types=1);

namespace Chiron\Handler\Formatter;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as Whoops;

use function class_exists;

// ajouter les informations sur la request de l'application !!!!
//https://github.com/zendframework/zend-expressive/blob/master/src/Middleware/WhoopsErrorResponseGenerator.php#L95
//https://github.com/zeuxisoo/php-slim-whoops/blob/master/src/Zeuxisoo/Whoops/Provider/Slim/WhoopsGuard.php#L64

//https://github.com/userfrosting/UserFrosting/blob/master/app/sprinkles/core/src/Error/Renderer/WhoopsRenderer.php
//https://github.com/filp/whoops/blob/master/src/Whoops/Handler/PrettyPageHandler.php

//https://github.com/narrowspark/framework/blob/ccda2dca0c312dbea08814d1372c1802920ebcca/src/Viserio/Component/Exception/Displayer/WhoopsPrettyDisplayer.php
//https://github.com/narrowspark/framework/blob/ccda2dca0c312dbea08814d1372c1802920ebcca/src/Viserio/Component/Exception/Displayer/WhoopsJsonDisplayer.php

class WhoopsFormatter implements FormatterInterface
{
    public function format(ServerRequestInterface $request, Throwable $e): string
    {
        return $this->whoops($request)->handleException($e);
    }

    /**
     * Get the whoops instance.
     *
     * @return \Whoops\Run
     */
    private function whoops(ServerRequestInterface $request): Whoops
    {
        // TODO : vérifier si on stock bien ces originalXXX variables en amont.
        $request = $request->getAttribute('originalRequest', false) ?: $request;

        $handler = new PrettyPageHandler();
        $handler->addDataTable('PSR7 Request Data', [
            'Method'                 => $request->getMethod(),
            'Protocol'               => $request->getProtocolVersion(),
            'URI'                    => (string) $request->getUri(),
            'Headers'                => $request->getHeaders(),
            'Cookies'                => $request->getCookieParams(),
            'Attributes'             => $request->getAttributes(),
            'Query Params'           => $request->getQueryParams(),
            'Body Params'            => $request->getParsedBody()
        ]);

        $whoops = new Whoops();
        $whoops->allowQuit(false);
        $whoops->writeToOutput(false);
        $whoops->pushHandler($handler);

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
        // TODO : ajouter aussi un test si la classe PrettyPageHandler::class existe !!!!
        return class_exists(Whoops::class);
    }
}
