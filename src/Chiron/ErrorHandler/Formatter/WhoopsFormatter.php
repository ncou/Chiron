<?php

declare(strict_types=1);

namespace Chiron\ErrorHandler\Formatter;

use Chiron\Support\VarDumper;
use function class_exists;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Whoops\Handler\HandlerInterface as WhoopsHandlerInterface;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as Whoops;

// ajouter les informations sur la request de l'application !!!!
//https://github.com/zendframework/zend-expressive/blob/master/src/Middleware/WhoopsErrorResponseGenerator.php#L95
//https://github.com/zeuxisoo/php-slim-whoops/blob/master/src/Zeuxisoo/Whoops/Provider/Slim/WhoopsGuard.php#L64

//https://github.com/userfrosting/UserFrosting/blob/master/app/sprinkles/core/src/Error/Renderer/WhoopsRenderer.php
//https://github.com/filp/whoops/blob/master/src/Whoops/Handler/PrettyPageHandler.php

//https://github.com/narrowspark/framework/blob/ccda2dca0c312dbea08814d1372c1802920ebcca/src/Viserio/Component/Exception/Displayer/WhoopsPrettyDisplayer.php
//https://github.com/narrowspark/framework/blob/ccda2dca0c312dbea08814d1372c1802920ebcca/src/Viserio/Component/Exception/Displayer/WhoopsJsonDisplayer.php

// TODO : ajouter une notion de "blacklist" pour ne pas afficher certaines balises dans la page whoops de debug (utile lorsqu'on oublie de changer le mode debug à false lorsqu'on pousse l'application en producion)
//https://github.com/filp/whoops/blob/master/src/Whoops/Handler/PrettyPageHandler.php#L801
//https://laravel.com/docs/5.8/configuration#hiding-environment-variables-from-debug

class WhoopsFormatter implements FormatterInterface
{
    public function format(ServerRequestInterface $request, Throwable $e): string
    {
        return $this->whoops($request)->handleException($e);
    }

    // TODO : c'est un test (lorsqu'on enregistre un set_exception_handler() ) à virer une fois le test terminé !!!!
    public function format2(Throwable $e): string
    {
        $whoops = new Whoops();
        $whoops->allowQuit(false);
        $whoops->writeToOutput(false);

        $handler = new PrettyPageHandler();
        // whoops does not know about RoadRunner
        $handler->handleUnconditionally(true);

        $whoops->pushHandler($handler);

        return $whoops->handleException($e);
    }

    /**
     * Get the whoops instance.
     *
     * @return \Whoops\Run
     */
    protected function whoops(ServerRequestInterface $request): Whoops
    {
        $whoops = new Whoops();
        $whoops->allowQuit(false);
        $whoops->writeToOutput(false);
        $whoops->pushHandler($this->makeWhoopsHandler($request));

        return $whoops;
    }

    protected function makeWhoopsHandler(ServerRequestInterface $request): WhoopsHandlerInterface
    {
        $handler = new PrettyPageHandler();

        // whoops does not know about RoadRunner
        $handler->handleUnconditionally(true);
        $handler->addDataTable('PSR7/Request Data', [
            'Method'                 => $request->getMethod(),
            'Protocol'               => $request->getProtocolVersion(),
            'URI'                    => (string) $request->getUri(),
            'Path'                   => $request->getUri()->getPath(),
            'Host'                   => $request->getUri()->getHost(),
            'Headers'                => $request->getHeaders() ?: '<none>',
            // TODO : vérifier si on doit utiliser un VarDumper cela permet d'avoir un tableau plus clair (on affiche une synthaxe courte avec les crochets "[]" au lieu de "Array(0 =>)") !!!!
            //'Headers'                => $request->getHeaders() ? VarDumper::export($request->getHeaders()) : '<none>',
            'Cookies'                => $request->getCookieParams() ?: '<none>',
            'Attributes'             => $request->getAttributes() ?: '<none>',
            'Query Params'           => $request->getQueryParams() ?: '<none>',
            'Body Params'            => $request->getParsedBody() ?: '<none>',
        ]);

        return $handler;
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
        // assert Whoops package is present.
        return class_exists(Whoops::class);
    }
}
