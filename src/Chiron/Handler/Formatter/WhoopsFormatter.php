<?php

declare(strict_types=1);

namespace Chiron\Handler\Formatter;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as Whoops;

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
        return $this->whoops()->handleException($e);
    }

    /**
     * Get the whoops instance.
     *
     * @return \Whoops\Run
     */
    private function whoops(): Whoops
    {
        $whoops = new Whoops();
        $whoops->allowQuit(false);
        $whoops->writeToOutput(false);
        $whoops->pushHandler(new PrettyPageHandler());

/*
        //$uri = $request->getAttribute('originalUri', false) ?: $request->getUri();
        //$request = $request->getAttribute('originalRequest', false) ?: $request;

        $serverParams = $request->getServerParams();

        $scriptName = $serverParams['SCRIPT_NAME'] ?? '';

        $handler->addDataTable('Chiron Application Request', [
            'HTTP Method'            => $request->getMethod(),
            'URI'                    => (string) $uri,
            'Script'                 => $scriptName,
            'Headers'                => $request->getHeaders(),
            'Cookies'                => $request->getCookieParams(),
            'Attributes'             => $request->getAttributes(),
            'Query String Arguments' => $request->getQueryParams(),
            'Body Params'            => $request->getParsedBody(),
        ]);

// TODO : autre exemple de ce que l'on peut ajouter.
        'HTTP Method'     => $this->request->getMethod(),
        'Base URL'        => (string) $this->request->getUri(),
        'Path'            => $this->request->getUri()->getPath(),
        'Query String'    => $this->request->getUri()->getQuery() ?: '<none>',
        'Scheme'          => $this->request->getUri()->getScheme(),
        'Port'            => $this->request->getUri()->getPort(),
        'Host'            => $this->request->getUri()->getHost(),

        // TODO : il manque les liste suivantes à afficher :
        //getServerParams / getUploadedFiles / getRequestTarget / getProtocolVersion

        */

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
