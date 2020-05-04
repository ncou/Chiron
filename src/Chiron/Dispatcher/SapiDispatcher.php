<?php

declare(strict_types=1);

namespace Chiron\Dispatcher;

use Chiron\Http\Http;
use Chiron\Http\Emitter\EmitterInterface;
use Chiron\Http\Emitter\SapiEmitter;
use Psr\Http\Message\ServerRequestInterface;
use Chiron\Facade\Request;
use Chiron\ErrorHandler\ErrorHandler;
use Chiron\Router\Method;
use Throwable;

final class SapiDispatcher extends AbstractDispatcher
{
    /**
     * {@inheritdoc}
     */
    public function canDispatch(): bool
    {
        return php_sapi_name() !== 'cli';
    }

    /**
     * @param Http   $http
     * @param EmitterInterface   $emitter
     * @param ErrorHandler   $errorHandler
     */
    // TODO : utiliser plutot un ErrorHandlerInterface au lieu de l'objet ErrorHandler !!!!
    protected function perform(Http $http, SapiEmitter $emitter, ErrorHandler $errorHandler): void
    {
        $request = Request::fromGlobals();

        // TODO : code à améliorer pour savoir si on est en debug ou non et donc si les exceptions doivent afficher le détail (stacktrace notamment) !!!!
        $verbose = true;

        // TODO : c'est quoi l'utilité de ce code (le try/catch Throwable) versus le code qui est déjà présent dans le ErrorHandlerMiddleware ????
        try {
            $response = $http->handle($request);
        } catch (Throwable $e) {
            $response = $errorHandler->renderException($e, $request, $verbose);
        }

        // emit the response without the body if the request is a 'GET' http method.
        $withoutBody = $request->getMethod() === Method::HEAD;
        $emitter->emit($response, $withoutBody);
    }
}
