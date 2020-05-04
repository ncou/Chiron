<?php

declare(strict_types=1);

namespace Chiron\Http;

use Chiron\Container\SingletonInterface;
use Chiron\Router\RequestHandler;
use Chiron\Router\RouterInterface;
use Chiron\Router\RoutingHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Facade\HttpDecorator;

// TODO : faire étendre cette classe de la classe Pipeline::class ?????
// TODO : utiliser une SplPriorityQueue pour ajouter des middlewares dans cette classe ????
// TODO : classe à renommer en HttpRunner ????
final class Http implements RequestHandlerInterface, SingletonInterface
{
    private $handler;

    /**
     * @var array MiddlewareInterface[]
     */
    private $stack = [];

    /**
     * Add middleware to the beginning of the stack (Prepend).
     *
     * @param string|callable|MiddlewareInterface|RequestHandlerInterface|ResponseInterface $middlewares It could also be an array of such arguments.
     *
     * @return self
     */
    public function addMiddlewaresOnTop($middlewares): self
    {
        // Keep the right order when adding an array to the top of the middlewares stack.
        if (is_array($middlewares)) {
            $middlewares = array_reverse($middlewares);
        }

        return $this->add($middlewares, true);
    }

    /**
     * Add middleware to the bottom of the stack by default (Append).
     *
     * @param string|callable|MiddlewareInterface|RequestHandlerInterface|ResponseInterface $middlewares It could also be an array of such arguments.
     * @param bool                                                                          $onTop       Force the middleware position on top of the stack
     *
     * @return self
     */
    public function addMiddlewares($middlewares, bool $onTop = false): self
    {
        if (! is_array($middlewares)) {
            $middlewares = [$middlewares];
        }

        foreach ($middlewares as $middleware) {
            if ($onTop) {
                //prepend Middleware
                array_unshift($this->stack, $middleware);
            } else {
                // append Middleware
                array_push($this->stack, $middleware);
            }
        }

        return $this;
    }

    /**
     * Execute the middleware stack seeded with the RoutingHandler as the last handler.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    // TODO : améliorer le code, initialiser l'objet Pipeline::class dans le constructeur + ajouter un setter pour injecter directement le middleware (utiliser un MiddlewareDecorator pour toujours avoir des objets MiddlewareInterface), et la méthode getStackMiddleware ne servira plus à rien !!!!
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->seedRequestHandler();

        return $this->handler->handle($request);
    }

    // TODO : réfléchir si le code ne peux pas être amélioré !!! je n'aime pas trop qu'on utilise une variable de classe $this->handler. Réfléchir si il n'est pas possible de passer l'ensemble de cette classe comme un objet handler, regarder du côté de la classe Pipeline c'est ce qu'elle fait !!!!
    private function seedRequestHandler(): void
    {
        if ($this->handler) {
            // The handler is already seeded, we get out!
            return;
        }

        $this->handler = new RequestHandler();

        //****************************
        // TODO : injecter de force dans cette méthode le middleware ErrorHandlerMiddlware::class au sommet de la stack ???? ou alors passer par un Bootloader pour faire cet ajout de middlware avant l'execution de la stack ???
        // TODO : déplacer le middleware de gestion des Errors ErrorHandlerMiddleware dans le répertoire "ErrorHandler", et forcer ici l'ajout au sommet da la pile des middleware et utilisant un décorateur pour résoudre le nom du middleware via le container.
        //****************************

        foreach ($this->stack as $middleware) {
            $this->handler->pipe(HttpDecorator::toMiddleware($middleware));
        }

        // add the default routing handler at the bottom of the stack, to execute the MatchingRoute handler.
        $this->handler->setFallback(HttpDecorator::toHandler(RoutingHandler::class));
    }
}
