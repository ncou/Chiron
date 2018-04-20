<?php

declare(strict_types=1);

// TODO : AUTH service : https://github.com/harikt/expressive-auth   +   https://github.com/auraphp/Aura.Auth

//TODO : stocker la réponse de base dans un objet : https://github.com/zendframework/zend-expressive/blob/release-2.2/src/Application.php#L101    "setResponsePrototype()"

// TODO : serviceProvider qui charge dans le container tout ce dont on a besoin : https://github.com/userfrosting/UserFrosting/blob/master/app/sprinkles/core/src/ServicesProvider/ServicesProvider.php
// et ici on utilise directement un fichier pour charger le container : https://github.com/phapi/phapi-configuration/blob/master/app/configuration/default/settings.php
// ici on configure directement les factory pour le container : https://github.com/zendframework/zend-pimple-config/blob/master/src/Config.php

// TODO : fichiers de configuration : prendre exemple ici : https://github.com/userfrosting/UserFrosting/blob/master/app/sprinkles/core/config/default.php    https://github.com/userfrosting/UserFrosting/blob/master/app/sprinkles/core/src/Core.php#L50

// TODO : ajouter un logger en paramétre, et si ce n'est pas une instance de LoggerInterface on initialise un NullLogger : https://github.com/phapi/log/blob/master/src/Phapi/Di/Validator/Log.php#L64

namespace Chiron;

// TODO : gérer les exceptions avec la remontée de decorator : https://github.com/thephpleague/route/blob/master/src/Strategy/JsonStrategy.php#L60
// https://github.com/nunomazer/projeto-suporte-curso-laravel/blob/master/vendor/symfony/debug/Symfony/Component/Debug/Exception/FlattenException.php

// TODO : utiliser la fonction "ex" pour gérer les tableaus de configuration de la sorte : ex($array, 'foo.bar.value') (https://github.com/swt83/php-ex)
// TODO : utiliser un service de ce style pour charger un fichier de configuration : https://github.com/igorw/ConfigServiceProvider/blob/master/src/Igorw/Silex/ConfigServiceProvider.php
// TODO : utiliser eventuellement ce fichier : https://github.com/lokhman/silex-config/blob/master/src/Silex/Provider/ConfigServiceProvider.php

// TODO : ajouter un faux cache : https://github.com/phapi/cache-nullcache

//*****************************
// TODO : regarder pour ajouter par défaut les middlewares dans la stack (à faire lorsqu'on va faire un run) sur le emitter/le requestHandler....etc : https://github.com/swoft-cloud/swoft-framework/blob/c105a87b667f06f01eddf0c17ff94f023008dae4/src/Web/DispatcherServer.php#L85
//*****************************

use Chiron\Config\Config;
use Chiron\Http\Response;
use Chiron\Routing\Route;
use Chiron\Routing\Router;
use Chiron\Stack\RequestHandlerStack;
use Chiron\Stack\Utils\CallableMiddlewareDecorator;
use Chiron\Stack\Utils\CallableRequestHandlerDecorator;
use Chiron\Stack\Utils\LazyLoadingMiddleware;
use Closure;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

class Application
{
    //@{ Framework details
    const
    PACKAGE = 'Chiron Framework';
    const VERSION = '1.0.0';
    //@}

    /**
     * The logger instance.
     *
     * @var LoggerInterface
     */
    private $logger;
    /**
     * Dependency injection container.
     *
     * @var ContainerInterface
     */
    private $container;
    /**
     * The router instance.
     *
     * @var RouterInterface // TODO : interface à créer !!!!
     */
    private $router;

    private $requestHandler;

    /**
     * Load a configuration file into the application.
     *
     * @param string $name
     *
     * @return void
     */
    //https://github.com/laravel/lumen-framework/blob/5.5/src/Application.php#L598
    /*
    public function configure($name)
    {
        if (isset($this->loadedConfigurations[$name])) {
            return;
        }
        $this->loadedConfigurations[$name] = true;
        $path = $this->getConfigurationPath($name);
        if ($path) {
            $this->make('config')->set($name, require $path);
        }
    }
*/

    /**
     * Add a middleware to the end of the stack.
     *
     * @param string|callable|MiddlewareInterface or an array of such arguments $middlewares
     *
     * @return $this (for chaining)
     */
    // TODO : gérer aussi les tableaux de middleware, ainsi que les tableaux de tableaux de middlewares
    public function middleware($middlewares)
    {
        if (!is_array($middlewares)) {
            $middlewares = [$middlewares];
        }

        foreach ($middlewares as $middleware) {
            $this->requestHandler->prepend($this->prepareMiddleware($middleware));
        }

        return $this;
    }

    /**
     * Decorate the middleware if necessary.
     *
     * @param string|callable|MiddlewareInterface $middleware
     *
     * @return MiddlewareInterface
     */
    private function prepareMiddleware($middleware) : MiddlewareInterface
    {
        if ($middleware instanceof MiddlewareInterface) {
            return $middleware;
        } elseif (is_callable($middleware)) {
            return new CallableMiddlewareDecorator($middleware);
        } elseif (is_string($middleware) && $middleware !== '') {
            return new LazyLoadingMiddleware($this->container, $middleware);
        } else {
            throw new InvalidArgumentException(sprintf(
                'Middleware "%s" is neither a string service name, a PHP callable, or a %s instance',
                is_object($middleware) ? get_class($middleware) : gettype($middleware),
                MiddlewareInterface::class
            ));
        }
    }

    /**
     * Configure whether to display PHP errors or silence them.
     *
     * Some of the settings affected here are redundant if the error handler is
     * overridden, but some of them pertain to errors which the error handler
     * does not receive, namely start-up errors and memory leaks.
     *
     * @param bool $debug Whether to display errors or silence them.
     */
    public function initErrorVisibility($debug = true)
    {
        /* Display startup errors which cannot be handled by the normal error handler. */
        ini_set('display_startup_errors', $debug);
        /* Display errors (redundant if the default error handler is overridden). */
        ini_set('display_errors', $debug);
        /* Report errors at all severity levels (redundant if the default error handler is overridden). */
        error_reporting($debug ? E_ALL : 0);
        /* Report detected memory leaks. */
        ini_set('report_memleaks', $debug);
    }

    /********************************************************************************
     * Router helper methods
     *******************************************************************************/

    /**
     * Add GET route.
     *
     * @param string                                    $pattern    The route URI pattern
     * @param callable|string                           $handler    The route callback routine
     * @param string|array|callable|MiddlewareInterface $middleware
     *
     * @return \Slim\Interfaces\RouteInterface
     */
    public function get(string $pattern, $handler, $middlewares = null)
    {
        return $this->route($pattern, $handler, $middlewares)->method('GET');
    }

    /**
     * Add HEAD route.
     *
     * HEAD was added to HTTP/1.1 in RFC2616
     *
     * @link https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
     *
     * @param string                                    $pattern    The route URI pattern
     * @param callable|string                           $handler    The route callback routine
     * @param string|array|callable|MiddlewareInterface $middleware
     *
     * @return \Slim\Interfaces\RouteInterface
     */
    // TODO : vérifier l'utilité de cette méthode. Et il manque encore la partie CONNECT et TRACE !!!! dans ces helpers
    public function head(string $pattern, $handler, $middlewares = null)
    {
        return $this->route($pattern, $handler, $middlewares)->method('HEAD');
    }

    /**
     * Add POST route.
     *
     * @param string                                    $pattern    The route URI pattern
     * @param callable|string                           $handler    The route callback routine
     * @param string|array|callable|MiddlewareInterface $middleware
     *
     * @return \Slim\Interfaces\RouteInterface
     */
    public function post(string $pattern, $handler, $middlewares = null)
    {
        return $this->route($pattern, $handler, $middlewares)->method('POST');
    }

    /**
     * Add PUT route.
     *
     * @param string                                    $pattern    The route URI pattern
     * @param callable|string                           $handler    The route callback routine
     * @param string|array|callable|MiddlewareInterface $middleware
     *
     * @return \Slim\Interfaces\RouteInterface
     */
    public function put(string $pattern, $handler, $middlewares = null)
    {
        return $this->route($pattern, $handler, $middlewares)->method('PUT');
    }

    /**
     * Add PATCH route.
     *
     * PATCH was added to HTTP/1.1 in RFC5789
     *
     * @link http://tools.ietf.org/html/rfc5789
     *
     * @param string                                    $pattern    The route URI pattern
     * @param callable|string                           $handler    The route callback routine
     * @param string|array|callable|MiddlewareInterface $middleware
     *
     * @return \Slim\Interfaces\RouteInterface
     */
    public function patch(string $pattern, $handler, $middlewares = null)
    {
        return $this->route($pattern, $handler, $middlewares)->method('PATCH');
    }

    /**
     * Add DELETE route.
     *
     * @param string                                    $pattern    The route URI pattern
     * @param callable|string                           $callable   The route callback routine
     * @param string|array|callable|MiddlewareInterface $middleware
     *
     * @return \Slim\Interfaces\RouteInterface
     */
    public function delete(string $pattern, $handler, $middlewares = null)
    {
        return $this->route($pattern, $handler, $middlewares)->method('DELETE');
    }

    /**
     * Add OPTIONS route.
     *
     * @param string                                    $pattern    The route URI pattern
     * @param callable|string                           $handler    The route callback routine
     * @param string|array|callable|MiddlewareInterface $middleware
     *
     * @return \Slim\Interfaces\RouteInterface
     */
    // TODO : vérifier l'utilité de cette méthode !!!!
    public function options(string $pattern, $handler, $middlewares = null)
    {
        return $this->route($pattern, $handler, $middlewares)->method('OPTIONS');
    }

    /**
     * Add route for any HTTP method.
     *
     * @param string                                    $pattern    The route URI pattern
     * @param callable|string                           $handler    The route callback routine
     * @param string|array|callable|MiddlewareInterface $middleware
     *
     * @return \Slim\Interfaces\RouteInterface
     */
    // TODO : voir si on conserve cette méthode (qui finalement est un alias de "->map()")
    public function any(string $pattern, $handler, $middlewares = null)
    {
        // TODO : il faudrait plutot laissé vide le setMethods([]) comme ca toutes les méthodes sont acceptées !!!!
        return $this->route($pattern, $handler, $middlewares)->setAllowedMethods(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']);
    }

    /**
     * Add route with multiple methods.
     *
     * @param string                                    $pattern    The route URI pattern
     * @param RequestHandlerInterface|callable|string   $handler    The route callback routine
     * @param string|array|callable|MiddlewareInterface $middleware
     *
     * @return RouteInterface
     */
    // TODO : créer une classe RouteInterface qui servira comme type de retour (il faudra aussi l'ajouter dans le use en début de classe) !!!!!
    // TODO : méthode à renommer en "route()" ????
    public function route(string $pattern, $handler, $middlewares = null) : Route
    {
        if (!isset($middlewares)) {
            $middlewares = [];
        } elseif (!is_array($middlewares)) {
            $middlewares = [$middlewares];
        }

        // bind the application in the function, so we could use "this" in the callable to access the application.
        // TODO : ce bind est aussi fait au niveau de la classe DeferredRequestHandler !!!! c'est pas bon car c'est fait en double
        // TODO : vérifier l'utilité de ce bind !!!!!!
        if ($handler instanceof Closure) {
            $handler = $handler->bindTo($this);
        }

        /*
        if (is_callable([$handler, 'setContainer']) && $this->container instanceof ContainerInterface) {
            $handler->setContainer($this->container);
        }

        if (is_array($handler) && is_callable([$handler[0], 'setContainer']) && $this->container instanceof ContainerInterface) {
            $handler[0]->setContainer($this->container);
        }
        */

        if (is_string($handler) || is_callable($handler)) {
            $handler = new DeferredRequestHandler($handler, $this->container);
        }

        $handlerStack = new RequestHandlerStack($handler);
        foreach ($middlewares as $middleware) {
            $handlerStack->prepend($this->prepareMiddleware($middleware));
        }

        return $this->getRouter()->map($pattern, $handlerStack);
    }

    /**
     * Mounts a collection of callbacks onto a base route.
     *
     * @param string   $baseRoute The route sub pattern to mount the callbacks on
     * @param callable $fn        The callback method
     */
    public function mount(string $prefix, Closure $closure)
    {
        // Track current base route
        $curBasePath = $this->getRouter()->getBasePath();
        // Build new base route string
        $this->getRouter()->setBasePath($curBasePath.$prefix);
        // Bind the $this var, to app instance.
        $closure = $closure->bindTo($this);
        //$callback = Closure::bind($closure, $this, get_class());
        // Call the callable
        $closure($this);
        // TODO : regarder ici pour des arguments à passer au mount : https://github.com/nezamy/route/blob/master/system/Route.php#L185
        //call_user_func_array($closure, $this->bindArgs($this->pramsGroup, $this->matchedArgs));
        // Restore original base route
        $this->getRouter()->setBasePath($curBasePath);
    }

    // TODO : ajouter des méthodes proxy pour : getRoutes / getNamedRoute / hasRoute ?????? voir même pour generateUri et getBasePath/setBasePath ??????

    // TODO : ajouter une interface pour le router, et faire en sorte que cette méthode ait un type de retour du genre "RouterInterface", et on pourra aussi créer une méthode "setRouter(RouterInterface $router)"
    public function getRouter()
    {
        //return $this->router;
        return $this->container->get('router');
    }

    /*
    // TODO : méthode à implémenter !!!!!!
        public function setRouter(RouterInterface $router)
        {
            $this->router = $router;
            return $this;
        }*/

    /*******************************************************************************
     * Container
     ******************************************************************************/

    /**
     * Get container.
     *
     * @return ContainerInterface|null
     */
    public function getContainer() : ?ContainerInterface
    {
        return $this->container;
    }

    /**
     * Set container.
     *
     * @param ContainerInterface $container
     *
     * @return Application Returns itself to support chaining.
     */
    // TODO : voir si on conserve cette méthode ?????
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /*******************************************************************************
     * Logger
     ******************************************************************************/
    // TODO : on met le logger plutot dans le container ???? via un serviceRegister par exemple : https://github.com/slimphp/Slim/blob/3.x/Slim/DefaultServicesProvider.php
    // ou ici :

    /**
     * Get logger.
     *
     * @return LoggerInterface
     */
    public function getLogger() : LoggerInterface
    {
        // If a logger hasn't been set, use NullLogger
        if (!$this->logger instanceof LoggerInterface) {
            $this->logger = new NullLogger();
            //$this->logger = new \Logger();
        }

        return $this->logger;
    }

    /**
     * Sets logger.
     *
     * @param LoggerInterface $logger
     *
     * @return Application Returns itself to support chaining.
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    // TODO : ajouter 2 méthodes : $app->pipeRoutingMiddleware();   et  $app->pipeDispatchMiddleware();   pour l'ajout du routerMiddleware lui passer au constructeur l'object Router qui a une ionterface RouterInterface !!!!!

    /** @var \Zend\Expressive\Application $app */
    /*
$app->pipe(\Zend\Stratigility\Middleware\OriginalMessages::class);
$app->pipe(\Zend\Stratigility\Middleware\ErrorHandler::class);
$app->pipe(\App\Action\StripTrailingSlashMiddleware::class);
$app->pipe(\App\Action\Redirects::class);
$app->pipe(\Zend\Expressive\Helper\ServerUrlMiddleware::class);
$app->pipeRoutingMiddleware();
$app->pipe(\Zend\Expressive\Middleware\ImplicitHeadMiddleware::class);
$app->pipe(\Zend\Expressive\Middleware\ImplicitOptionsMiddleware::class);
$app->pipe(\Zend\Expressive\Helper\UrlHelperMiddleware::class);
$app->pipeDispatchMiddleware();
$app->pipe(\Zend\Expressive\Middleware\NotFoundHandler::class);
*/

    /********************************************************************************
     * Magic methods for convenience
     *******************************************************************************/

    // TODO : déplacer le container hors de l'appli, et mettre ces méthodes dans la classe du container.
    // MAGIC METHODS
    /*
    public function __get($property){
        return $this->getContainer()->get($property);
    }

    public function __isset($property){
        //return $this->offsetExists($property);
        return $this->getContainer()->has($property);
    }
    */

    /**
     * Calling a non-existant method on App checks to see if there's an item
     * in the container that is callable and if so, calls it.
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    //https://github.com/slimphp/Slim/blob/3.x/Slim/App.php#L117
    /*
    public function __call($method, $args)
    {
        if ($this->container->has($method)) {
            $obj = $this->container->get($method);
            if (is_callable($obj)) {
                return call_user_func_array($obj, $args);
            }
        }
        throw new \BadMethodCallException("Method $method is not a valid method");
    }
    */

    // TODO : regarder les méthodes magiques qu'il manque : https://github.com/ncou/klein.php-container-services-router/blob/master/src/Klein/ServiceProvider.php#L412
    // __set / __unset

    /*******************************************************************************
     * Constructor
     ******************************************************************************/

    /**
     * Instantiate a new Application.
     *
     * Objects and parameters can be passed as argument to the constructor.
     *
     * @param array $values The parameters or objects.
     */
    // TODO : lui passer un container en paramétre ?????
    public function __construct(array $values = [])
    { //, ContainerInterface $container = null){

        //$this->router = new Router();

        $this->container = new Container();

        $this->container->bind('router', function ($c) {
            return new Router();
        });

        //$this->container = $ontainer;

        $config = new Config($values);
        $this->container->config = $config;

        // TODO : créer plutot une classe "EmptyResponseHandler" qui utilisera une responseFactory pour renvoyer une response vide. et c'est cette classe qu'on passera à la Stack.
        $emptyResponse = new CallableRequestHandlerDecorator(function ($request) {
            // TODO : passer le charset + version http 1.1 par défaut à cette réponse !!!!
            //$this->container['charset'] et $this->container['httpVersion']
            $response = new Response();

            return $response;
        });

        $this->requestHandler = new RequestHandlerStack($emptyResponse);

        //$this->factory = new MiddlewareFactory($this->container);

        //$this->loadConfig($config_path_or_file_or_array, $config_cache_file);

        // TODO : déplacer ces initialisations dans le constructeur d'une classe CONTAINER externalisée

        // TODO : ajouter l'initialisation d'un logger ?????

        // TODO : vérifier l'utilité de mettre cela dans un container, normalement on va toujours passer par le router, donc le mettre dans un container n'est pas vraiment nécessaire, surtout que dans les controller on ne va pas réutiliser le router, car la méthode redirect ou getPathFor se trouve directement dans $app et pas dans la classe Router.
        // register the router in the pimple container

        /*
            $this['session'] = function ($c) {
                // TODO : déplacer la classe session dans le répertoire "components"
                return new Session();
            };
        */

        /*
            $this['router'] = function ($c) {
                return new Router($c->get('basePath'), $this->container);
            };
        */

        // Create request class closure.
        /*
            $this['request'] = function ($c) {
                return Request::fromGlobals();
            };
        */

        // TODO : à virer car maintenant la réponse est créée directement dans le controler. il faudrait plutot utiliser une ResponseFactory appellé directement dans le controller !!!
        // TODO : vérifier l'utilité de créer cette response ici !!!!! normalement chaque controller ou errorhandler va créer une nouvelle response...
        $this->container['response'] = function ($c) {

        //$headers = new Headers(['Content-Type' => 'text/html; charset=UTF-8']);
            //$response = new Response(200, $headers);
            //return $response->withProtocolVersion($container->get('settings')['httpVersion']);

            // TODO : à améliorer il faut passer le header text/heml et charset UTF8 par défaut + le code de réposne à 200 + si c'est du http ou https !!!!!!!!!
            $response = new Response();
            // TODO : ajouter 2 lignes "Content-Type" avec text/html puis avec charset=XXXX, ca fera la même chose qu'une ligne séparée avec une virgule
            // TODO : récuperer le charset directement dans la partie ->getContainer()->get('config') !!!!
            $response = $response->withAddedHeader('Content-Type', 'text/html; charset='.$c->get('charset'));
            //$response->setProtocolVersion($c->get('httpVersion'));
            return $response;
        };

        // TODO : initialiser un logger ici ???? et éventuellement créer une propriété pour changer le formater dans la restitution de la log. cf nanologger et la liste des todo pour mettre un formater custom à passer en paramétre du constructeur !!!!

        // register the not found route exception
        $this->container['404notFoundHandler'] = function ($c) {
            return function ($request, $response, $e) use ($c) {
                // TODO : penser à récupérer le charset déclaré dans le container, et le passer à la response !!!!
                return $response->withStatus($e->getStatusCode())
                    //->withHeader('Content-type', 'text/html')
                    ->setBody('404 page not found!');
            };
        };

        // register the method not allowed exception
        $this->container['httpExceptionHandler'] = function ($c) {
            return function ($request, $response, $e) use ($c) {
                // TODO : penser à récupérer le charset déclaré dans le container, et le passer à la response !!!!
                $response = $response->withStatus($e->getStatusCode())
                //->withHeader('Content-type', 'text/html')
                ->setBody($e->getMessage());

                foreach ($e->getHeaders() as $header => $value) {
                    $response = $response->withHeader($header, $value);
                }

                return $response;
            };
        };

        // TODO : à virer suite au passage sous PHP7 !!!!!!!!!!!
        $this->container['errorHandler'] = function ($c) {
            return function ($request, $response, $e) use ($c) {
                // TODO : corriger la méthode "expectsJson()"

//                  if ($request->expectsJson()) {
//                $response->setStatusCode(500)
//                  ->addHeader('Content-Type', 'application/json')
//                    ->setBody(json_encode(['status_code'   => $e->getCode(), 'reason_phrase' => $e->getMessage()]));
//                } else {

                // TODO : penser à récupérer le charset déclaré dans le container, et le passer à la response !!!!
                return $response->withStatus(502)
                    //->withHeader('Content-Type', 'text/html')
                    ->setBody('Exception1: '."Code: ({$e->getCode()}); Message: ({$e->getMessage()}); in file: {$e->getFile()} [line: {$e->getLine()}]");
                //->setBody('Something went wrong!');
            //    }
                //return $response;
            };
        };

        $this->container['phpErrorHandler'] = function ($c) {
            return function (ServerRequestInterface $request, ResponseInterface $response, \Throwable $e) use ($c) {
                // TODO : corriger la méthode "expectsJson()"

//                  if ($request->expectsJson()) {
//                $response->setStatusCode(500)
//                  ->addHeader('Content-Type', 'application/json')
//                    ->setBody(json_encode(['status_code'   => $e->getCode(), 'reason_phrase' => $e->getMessage()]));
//                } else {

                // TODO : penser à récupérer le charset déclaré dans le container, et le passer à la response !!!!
                return $response->withStatus(551)
                    //->withHeader('Content-Type', 'text/html')
                    ->setBody('Exception2: '."Code: ({$e->getCode()}); Message: ({$e->getMessage()}); in file: {$e->getFile()} [line: {$e->getLine()}]"); //$e->getTraceAsString()
                    //->setBody('Something went wrong!');
            //    }
                //return $response;
            };
        };

        /*
            $this->container['callableResolver'] = function ($container) {
                return new CallableResolver($container);
            };
        */

        //        $this->container['debug'] = false;
//        $this->container['charset'] = 'UTF-8';
//        $this->container['httpVersion'] = '1.1';
//        $this->container['logger'] = null;

        // TODO : il faut s'assurer que le charset est bien propagé dans la création de la response !!!! et mettre en minuscule cette valeur !!!!!
        $this->container['charset'] = 'UTF-8';
        $this->container['httpVersion'] = '1.1';
        $this->container['basePath'] = '';

        foreach ($values as $key => $value) {
            $this->container[$key] = $value;
        }

        //$router->setBasePath($request->getUri()->getBasePath());
        //$this->basePath = $this->container['basePath'];
        //$this->setBasePath($this->container['basePath']);

        $this->getRouter()->setBasePath($this->container->config['settings.basePath']);

        // initialise the Router constructor
    //parent::__construct($this->basePath, $this->container);

/*
//https://github.com/laravel/lumen-framework/blob/5.5/src/Application.php#L91
    if (! empty(env('APP_TIMEZONE'))) {
        date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));
    }
*/

    // TODO : à améliorer
    //$this->basePath = $basePath;
    // TODO : tester les fonctions "path()" et basePath() dans le cas ou la configuration du basePath n'a pas été spécifiée dans le constructeur de l'application. je pense qu'on aura une erreur :(

    //$this->basePath = $this['basePath'];

    //$this['router']->setBasePath($this->basePath);

/*
    if (isset($settings['charset']))
    {
        // Set the system character set
        Kohana::$charset = strtolower($settings['charset']);
    }
    if (function_exists('mb_internal_encoding'))
    {
        // Set the MB extension encoding to the same character set
        mb_internal_encoding(Kohana::$charset);
    }
    if (isset($settings['base_url']))
    {
        // Set the base URL
        Kohana::$base_url = rtrim($settings['base_url'], '/').'/';
    }
*/

/*
// TODO : regarder cette initialisation trouvée dans le framework de FatFree !!!!
  // Managed directives
  ini_set('default_charset',$charset='UTF-8');
  if (extension_loaded('mbstring'))
    mb_internal_encoding($charset);
  ini_set('display_errors',0);
  // Deprecated directives
  @ini_set('magic_quotes_gpc',0);
  @ini_set('register_globals',0);
  // Intercept errors/exceptions; PHP5.3-compatible
  $check=error_reporting((E_ALL|E_STRICT)&~(E_NOTICE|E_USER_NOTICE));
  set_exception_handler(
    function($obj) {
      $this->hive['EXCEPTION']=$obj;
      $this->error(500,
        $obj->getmessage().' '.
        '['.$obj->getFile().':'.$obj->getLine().']',
        $obj->gettrace());
    }
  );
  set_error_handler(
    function($level,$text,$file,$line) {
      if ($level & error_reporting())
        $this->error(500,$text,NULL,$level);
    }
  );


  date_default_timezone_set($this->hive['TZ']);
  // Register framework autoloader
  spl_autoload_register([$this,'autoload']);
  // Register shutdown handler
  register_shutdown_function([$this,'unload'],getcwd());
*/

//      $this->registerPhpErrorHandling();
    }

    //! Prohibit cloning
    // TODO : vérifier l'utilité de cette fonction
    /*
    private function __clone() {
    }
*/

    /**
     * @throws LogicException If trying to clone an instance of a singleton.
     *
     * @return void
     */
    /*
    final private function __clone()
    {
        throw new LogicException(
            sprintf(
                'Cloning "%s" is not allowed.',
                get_called_class()
            )
        );
    }
    */
    /**
     * @throws LogicException If trying to unserialize an instance of a singleton.
     *
     * @return void
     */
    /*
    final private function __wakeup()
    {
        throw new LogicException(
            sprintf(
                'Unserializing "%s" is not allowed.',
                get_called_class()
            )
        );
    }
    */

    /*******************************************************************************
     * Run App
     ******************************************************************************/

    public function run() : ResponseInterface
    {
        //$request = Request::createFromGlobals($_SERVER);
        $request = (new \Chiron\Http\Factories\ServerRequestFactory())->createServerRequestFromArray($_SERVER);

        return $this->process($request);
    }

    public function process(ServerRequestInterface $request) : ResponseInterface
    {

        // apply PHP config settings.
        $this->boot();

        // TODO : c'est plutot ici qu'on devrait la request::fromGlobals et éventuellement la mettre dans le container, non ????, avec éventuellement la possibilité de lui passer une request en paramétre de la méthode Run($request) et donc on créé la request ou on utilisera celle passée en paramétre. Celka peut servir si un utilisateur créé sa request avant (dans le cas ou il utilise du PSR7 request), mais aussi servir pour nos tests PHPUNIT avec une request pré-initilalisée. Cela peut aussi permettre à l'utilisateur de modifier la request, genre pour un sanitize, ou alors pour changer le type de méthode si il y a un champ formulaire hidden _method pour faire un override de la méthode http.

        // create response
        /*
        $headers = new Headers(['Content-Type' => 'text/html; charset=UTF-8']);
        $httpVersion = $this->getSetting('httpVersion');
        $response = new Response(200, $headers);
        $response = $response->withProtocolVersion($httpVersion);
        */

        $response = $this->requestHandler->handle($request);

        return $response;

        //'text/plain; q=0.5, application/json; version=1.0, application/xml; version=2.0;'
//'text/plain;q=0.5,text/html,text/*;q=0.1'

/*
        $request = $request->withHeader('Accept','text/plain; q=0.5, application/json; version=1.0, application/xml; version=2.0;');
        die(print_r($request->getAcceptableContentTypes()));

        $request = $request->withHeader('Accept-Language', 'fr;q=0.9, fr-CH, en;q=0.7, de;q=0.8, *;q=0.5');
        die(print_r($request->getAcceptableLanguages()));
*/

/*
      if ($response->isSuccessful() && $this->requestIsConditional()) {
            if (! $response->headers->has('ETag')) {
                $response->setEtag(md5($response->getContent()));
            }
            $response->isNotModified($request);
        }
*/
    }

    /**
     * set php settings from the defined config values.
     */
    private function boot()
    {
        // Set up any global PHP settings from the config service.
        $config = $this->container->config;

        // TODO : regarder ici pour configurer les paramétres PHP pour logger les erreurs+exceptions : https://github.com/zendtech/zend-server-php-buildpack/blob/master/conf/zend/etc/php.ini   +   https://www.loggly.com/ultimate-guide/php-logging-basics/     +     http://cgit.drupalcode.org/drupalci_environments/tree/php/7.1-apache/conf/php/php-cli.ini?id=d75d2dd15e88674b150c1bdf1de26169ad5cbcb2

        // Display PHP fatal errors natively.
        // TODO : faire la même chose pour display_startup_errors !!!!!!!!!!!!!!!!!
        if (isset($config['php.display_errors'])) {
            ini_set('display_errors', $config['php.display_errors']);
        }
        // Log PHP fatal errors
        if (isset($config['php.log_errors'])) {
            ini_set('log_errors', $config['php.log_errors']);
        }
        // Configure error-reporting level
        if (isset($config['php.error_reporting'])) {
            error_reporting($config['php.error_reporting']);
        }
        // Configure time zone
        if (isset($config['php.timezone'])) {
            //ini_set('date.timezone', $config['php.timezone']);
            date_default_timezone_set($config['php.timezone']);
        }
    }

    /**
     * Set PHP configuration settings.
     *
     * @param array  $settings
     * @param string $prefix   Key prefix to prepend to array values (used to map . separated INI values)
     *
     * @return Zend_Application
     */
    private function setPhpSettings(array $settings)
    {
        foreach ($settings as $key => $value) {
            if (is_scalar($value)) {
                //ini_set($key, $value);
            }
        }

        return $this;
    }

    /**
     * The base path of the application installation.
     *
     * @var string
     */
    //protected $basePath;
    /**
     * Get the path to the application "app" directory.
     *
     * @return string
     */
    //https://github.com/laravel/lumen-framework/blob/5.5/src/Application.php#L706

    public function path()
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'app';
    }

    /**
     * Get the base path for the application.
     *
     * @param string|null $path
     *
     * @return string
     */
    public function basePath($path = null)
    {
        if (isset($this->basePath)) {
            return $this->basePath.($path ? '/'.$path : $path);
        }

        // TODO : tester le cas quand on n'a pas déclaré dans l'application un paramétre de basePath passé au constructeur de l'application. Je pense que renvoyer "/" va poser un soucis !!!!
        return '/';
    }

    //https://github.com/Kajna/K-Core/blob/master/Core/Util/Util.php#L21

    /**
     * Get site base url.
     *
     * @param string $path
     *
     * @return string
     */
    public static function base($path = '')
    {
        // Check for cached version of base path
        if (null !== self::$base) {
            return self::$base.$path;
        }
        if (isset($_SERVER['HTTP_HOST'])) {
            $base_url = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
            $base_url .= '://'.$_SERVER['HTTP_HOST'];
            $base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
            self::$base = $base_url;

            return $base_url.$path;
        }

        return '';
    }

    // TODO : dans un fichier séparé genre define.php :

/*
<?php
use \Swoft\App;
// Constants
!defined('DS') && define('DS', DIRECTORY_SEPARATOR);
// 系统名称
!defined('APP_NAME') && define('APP_NAME', 'swoft');
// 基础根目录
!defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__, 1));
// cli命名空间
!defined('COMMAND_NS') && define('COMMAND_NS', "App\Commands");
// 注册别名
$aliases = [
    '@root'       => BASE_PATH,
    '@app'        => '@root/app',
    '@res'        => '@root/resources',
    '@runtime'    => '@root/runtime',
    '@configs'    => '@root/config',
    '@resources'  => '@root/resources',
    '@beans'      => '@configs/beans',
    '@properties' => '@configs/properties',
    '@commands'   => '@app/Commands'
];
App::setAliases($aliases);
*/
}
