<?php

declare(strict_types=1);

namespace Chiron\Http;

use Chiron\Container\SingletonInterface;
use Chiron\Facade\HttpDecorator;
use Chiron\Routing\RequestHandler;
use Chiron\Routing\RoutingHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SplPriorityQueue;
use Chiron\Container\BindingInterface;

// TODO : faire étendre cette classe de la classe Pipeline::class ????? ou fusionner le code ????
// TODO : utiliser une SplPriorityQueue pour ajouter des middlewares dans cette classe ????
// TODO : classe à renommer en HttpRunner ???? et ajouter une méthode run() qui effectue un reset de l'index à 0 et execute ensuite la méthode handle() [exemple : https://github.com/middlewares/utils/blob/master/src/Dispatcher.php#L44]
// TODO : créer un constructeur et lui passer l'objet MiddlewareDecorator, et utiliser la méthode decorate de cette classe lorsqu'on ajoute un middleware au tableau.
// TODO : renommer la classe en HttpRunner
final class Http implements SingletonInterface
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

    private $binder;

    /**
     * @var array MiddlewareInterface[]
     */
    // TODO : attention le @var est faux, pour l'instant la variuable $stack peut contenir des callable, des string...etc
    private $queue;

    // TODO : initialiser l'objet Pipeline::class dans le constructeur, et ne plus utiliser la méthode seedRequestHandler en ajoutant au fur et à mesure les middlewares décorés dans la queue.
    public function __construct(BindingInterface $binder)
    {
        $this->binder = $binder;
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
    public function run(ServerRequestInterface $request): ResponseInterface
    {
        $this->seedRequestHandler();
        $this->bindRequestInstance($request);

        // TODO : à virer c'est un test !!!!
        //$req = container(ServerRequestInterface::class);
        //dd($req->getUri()->getPath());
        //
        //$context = container(\Chiron\Http\RequestContext::class);
        //dd($context->root());
        //
        //
        //$urlGenerator = container(\Chiron\Router\UrlGeneratorInterface::class);
        //dd($urlGenerator->getRoutes());
        //dd($urlGenerator->relativeUrlFor('home', ['action' => 'index']));
        //dd($urlGenerator->absoluteUrlFor(new \Nyholm\Psr7\Uri('http://127.0.0.1'), 'home', ['action' => 'index']));
        //
        //dd($urlGenerator->relativeUrlFor('dot_test'));

        return $this->handler->handle($request);
    }

    // TODO : réfléchir si le code ne peux pas être amélioré !!! je n'aime pas trop qu'on utilise une variable de classe $this->handler.
    private function seedRequestHandler(): void
    {
        if ($this->handler) {
            // The handler is already seeded, we get out!
            return;
        }

        $this->handler = new RequestHandler();

        foreach ($this->queue as $middleware) {
            $this->handler->pipe(HttpDecorator::toMiddleware($middleware));
        }

        // add the default routing handler at the bottom of the stack.
        $this->handler->setFallback(HttpDecorator::toHandler(RoutingHandler::class));
    }

    /**
     * Store a "fresh" request instance in the container.
     */
    private function bindRequestInstance(ServerRequestInterface $request): void
    {
        // Requests are considered immutable, so a simple "bind" is enough.
        $this->binder->bind(ServerRequestInterface::class, $request);
    }
}
