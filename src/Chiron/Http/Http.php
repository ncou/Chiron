<?php

declare(strict_types=1);

namespace Chiron\Http;

use Chiron\Container\SingletonInterface;
use Chiron\Facade\HttpDecorator;
use Chiron\Router\RequestHandler;
use Chiron\Router\RoutingHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SplPriorityQueue;

// TODO : faire étendre cette classe de la classe Pipeline::class ????? ou fusionner le code ????
// TODO : utiliser une SplPriorityQueue pour ajouter des middlewares dans cette classe ????
// TODO : classe à renommer en HttpRunner ???? et ajouter une méthode run() qui effectue un reset de l'index à 0 et execute ensuite la méthode handle() [exemple : https://github.com/middlewares/utils/blob/master/src/Dispatcher.php#L44]
// TODO : créer un constructeur et lui passer l'objet MiddlewareDecorator, et utiliser la méthode decorate de cette classe lorsqu'on ajoute un middleware au tableau.
// TODO : virer l'interface RequestHandlerInterface une fois que la classe est renommée en HttpRunner + renommer la méthode handle() en run()
final class Http implements RequestHandlerInterface, SingletonInterface
{
    // TODO : externaliser ces constantes dans une classe "Priority" ????
    /** @var int */
    public const MIN = -300;
    /** @var int */
    public const LOW = -200;
    /** @var int */
    public const BELOW_NORMAL = -100;
    /** @var int */
    public const NORMAL = 0;
    /** @var int */
    public const ABOVE_NORMAL = 100;
    /** @var int */
    public const HIGH = 200;
    /** @var int */
    public const MAX = 300;

    /**
     * @var int Seed used to ensure queue order for items of the same priority
     */
    private $serial = PHP_INT_MAX;

    private $handler;

    /**
     * @var array MiddlewareInterface[]
     */
    // TODO : attention le @var est faux, pour l'instant la variuable $stack peut contenir des callable, des string...etc
    private $stack = [];

    private $queue;

    public function __construct()
    {
        $this->queue = new SplPriorityQueue();
    }

    /**
     * Insert middleware in the queue with a given priority.
     *
     * Utilizes {@var $serial} to ensure that values of equal priority are
     * emitted in the same order in which they are inserted.
     *
     * @param string|callable|MiddlewareInterface|RequestHandlerInterface|ResponseInterface $middleware
     * @param int                                                                           $priority
     *
     * @return self
     */
    // TODO : remonter l'appel au HttpDecorator::toMiddleware() dans cette méthode ci dessous !!!!
    public function addMiddleware($middleware, int $priority = self::NORMAL): self
    {
        $this->queue->insert($middleware, [$priority, $this->serial--]);

        return $this;
    }

    /**
     * Add middleware to the beginning of the stack (Prepend).
     *
     * @param string|callable|MiddlewareInterface|RequestHandlerInterface|ResponseInterface $middlewares It could also be an array of such arguments.
     *
     * @return self
     */
    /*
    public function addMiddlewaresOnTop($middlewares): self
    {
        // Keep the right order when adding an array to the top of the middlewares stack.
        if (is_array($middlewares)) {
            $middlewares = array_reverse($middlewares);
        }

        return $this->add($middlewares, true);
    }*/

    /**
     * Add middleware to the bottom of the stack by default (Append).
     *
     * @param string|callable|MiddlewareInterface|RequestHandlerInterface|ResponseInterface $middlewares It could also be an array of such arguments.
     * @param bool                                                                          $onTop       Force the middleware position on top of the stack
     *
     * @return self
     */
    /*
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
    }*/

    /**
     * Execute the middleware stack seeded with the RoutingHandler as the last handler.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    // TODO : améliorer le code, initialiser l'objet Pipeline::class dans le constructeur + ajouter un setter pour injecter directement le middleware (utiliser un MiddlewareDecorator pour toujours avoir des objets MiddlewareInterface), et la méthode getStackMiddleware ne servira plus à rien !!!!
    // TODO : Méthode à renommer en "run()"
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

        /*
                foreach ($this->stack as $middleware) {
                    $this->handler->pipe(HttpDecorator::toMiddleware($middleware));
                }
        */
        foreach ($this->queue as $middleware) {
            $this->handler->pipe(HttpDecorator::toMiddleware($middleware));
        }

        // add the default routing handler at the bottom of the stack, to execute the MatchingRoute handler.
        $this->handler->setFallback(HttpDecorator::toHandler(RoutingHandler::class));
    }
}
