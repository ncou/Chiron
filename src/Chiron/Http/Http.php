<?php

declare(strict_types=1);

namespace Chiron\Http;

use Chiron\Container\SingletonInterface;
use Chiron\Router\RequestHandler;
use Chiron\Router\RouterInterface;
use Chiron\Router\RoutingHandler;
use Chiron\Router\Traits\MiddlewareAwareInterface;
use Chiron\Router\Traits\MiddlewareAwareTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

// TODO : faire étendre cette classe de la classe Pipeline::class ?????
// TODO : utiliser une SplPriorityQueue pour ajouter des middlewares dans cette classe ????
// TODO : ajouter un MiddlewareFactory ou MiddlewareDecorator dans le constructeur pour décorer les middleware de type string ou callable !!!!
class Http implements RequestHandlerInterface, MiddlewareAwareInterface, SingletonInterface
{
    // TODO : vérifier si on a vraiment besoin du trait MiddlewareAwareTrait
    use MiddlewareAwareTrait;

    //RouterInterface
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
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
        // TODO : injecter de force dans cette méthode le middleware ErrorHandlerMiddlware::class au sommet de la stack ???? ou alors passer par un Bootloader pour faire cet ajout de middlware avant l'execution de la stack ???
        $handler = new RequestHandler();

        // TODO : passer par un MiddlewareDecorator::class pour gérer le cas ou le middleware qui est dans ce tableau est un string qu'on doit aller chercher dans le container par exemple. Dans ce cas il faudra un LazyMiddleware pour décorer cette string.
        foreach ($this->getMiddlewareStack() as $middleware) {
            $handler->pipe($middleware);
        }

        // TODO : il n'y a pas moyen de faire un code plus propre que cela ? il faudrait éviter de passer dans le constructeur un objet Router::class mais directement un RoutingHandler::class. non ????
        $handler->setFallback(new RoutingHandler($this->router));

        return $handler->handle($request);
    }
}
