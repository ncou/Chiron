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

// TODO : vérifier si les extensions de base sont bien activées l'extension 'intl' est bien chargée sinon lever une exception, ca sert pour le middleware referralspam pour convertir le domain et punycode
/*
if (! extension_loaded('intl')) {
    throw new RuntimeException('Intl extension is not available.');
}
if (! extension_loaded('openssl')) {
   throw new RuntimeException('OpenSSL extension is not available.');
}
if (! extension_loaded('mbstring')) {
   throw new RuntimeException('Multibyte String extension is not available.');
}

*/

use Chiron\Config\Config;
use Chiron\Container\Container;
// TODO : virer la classe CallableRequestHandlerDecorator !!!!!!!!!!!!!
//use Chiron\Handler\CallableRequestHandlerDecorator;
use Chiron\Handler\DeferredRequestHandler;
use Chiron\Handler\FixedResponseHandler;
use Chiron\Handler\Stack\Decorator\CallableMiddlewareDecorator;
use Chiron\Handler\Stack\Decorator\LazyLoadingMiddleware;
use Chiron\Handler\Stack\RequestHandlerStack;
use Chiron\Http\Psr\Response;
use Chiron\Routing\Route;
use Chiron\Routing\RouteGroup;
use Closure;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Log\LoggerInterface;

class Application
{
    public const VERSION = '1.0.0';

    /**
     * The logger instance.
     *
     * @var LoggerInterface
     */
    //private $logger;

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
    //private $router;

    private $requestHandler;

    /**
     * Load a configuration file into the application.
     *
     * @param string $name
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
        if (! is_array($middlewares)) {
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
    private function prepareMiddleware($middleware): MiddlewareInterface
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
     * @param bool $debug whether to display errors or silence them
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
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.1
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.3
     *
     * @param string                                    $pattern    The route URI pattern
     * @param callable|string                           $handler    The route callback routine
     * @param string|array|callable|MiddlewareInterface $middleware
     *
     * @return \Chiron\Routing\Route
     */
    public function get(string $pattern, $handler, $middlewares = null)
    {
        return $this->map($pattern, $handler, $middlewares)->method('GET');
    }

    /**
     * Add HEAD route.
     *
     * HEAD was added to HTTP/1.1 in RFC2616
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.2
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
     *
     * @param string                                    $pattern    The route URI pattern
     * @param callable|string                           $handler    The route callback routine
     * @param string|array|callable|MiddlewareInterface $middleware
     *
     * @return \Chiron\Routing\Route
     */
    // TODO : vérifier l'utilité de cette méthode. Et il manque encore la partie CONNECT et TRACE !!!! dans ces helpers
    public function head(string $pattern, $handler, $middlewares = null)
    {
        return $this->map($pattern, $handler, $middlewares)->method('HEAD');
    }

    /**
     * Add POST route.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.3
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.5
     *
     * @param string                                    $pattern    The route URI pattern
     * @param callable|string                           $handler    The route callback routine
     * @param string|array|callable|MiddlewareInterface $middleware
     *
     * @return \Chiron\Routing\Route
     */
    public function post(string $pattern, $handler, $middlewares = null)
    {
        return $this->map($pattern, $handler, $middlewares)->method('POST');
    }

    /**
     * Add PUT route.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.4
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.6
     *
     * @param string                                    $pattern    The route URI pattern
     * @param callable|string                           $handler    The route callback routine
     * @param string|array|callable|MiddlewareInterface $middleware
     *
     * @return \Chiron\Routing\Route
     */
    public function put(string $pattern, $handler, $middlewares = null)
    {
        return $this->map($pattern, $handler, $middlewares)->method('PUT');
    }

    /**
     * Add PATCH route.
     *
     * PATCH was added to HTTP/1.1 in RFC5789
     *
     * @see http://tools.ietf.org/html/rfc5789
     *
     * @param string                                    $pattern    The route URI pattern
     * @param callable|string                           $handler    The route callback routine
     * @param string|array|callable|MiddlewareInterface $middleware
     *
     * @return \Chiron\Routing\Route
     */
    public function patch(string $pattern, $handler, $middlewares = null)
    {
        return $this->map($pattern, $handler, $middlewares)->method('PATCH');
    }

    /**
     * Add PURGE route.
     *
     * PURGE is not an official method, and there is no RFC for the moment.
     *
     * @param string                                    $pattern    The route URI pattern
     * @param callable|string                           $handler    The route callback routine
     * @param string|array|callable|MiddlewareInterface $middleware
     *
     * @return \Chiron\Routing\Route
     */
    public function purge(string $pattern, $handler, $middlewares = null)
    {
        return $this->map($pattern, $handler, $middlewares)->method('PURGE');
    }

    /**
     * Add DELETE route.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.5
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.7
     *
     * @param string                                    $pattern    The route URI pattern
     * @param callable|string                           $callable   The route callback routine
     * @param string|array|callable|MiddlewareInterface $middleware
     *
     * @return \Chiron\Routing\Route
     */
    public function delete(string $pattern, $handler, $middlewares = null)
    {
        return $this->map($pattern, $handler, $middlewares)->method('DELETE');
    }

    /**
     * Add OPTIONS route.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.7
     *
     * @param string                                    $pattern    The route URI pattern
     * @param callable|string                           $handler    The route callback routine
     * @param string|array|callable|MiddlewareInterface $middleware
     *
     * @return \Chiron\Routing\Route
     */
    // TODO : vérifier l'utilité de cette méthode !!!!
    public function options(string $pattern, $handler, $middlewares = null)
    {
        return $this->map($pattern, $handler, $middlewares)->method('OPTIONS');
    }

    // TODO : ajouter le support pour les méthodes TRACE et CONNECT ????

    /**
     * Add route for any HTTP method.
     *
     * @param string                                    $pattern    The route URI pattern
     * @param callable|string                           $handler    The route callback routine
     * @param string|array|callable|MiddlewareInterface $middleware
     *
     * @return \Chiron\Routing\Route
     */
    // TODO : voir si on conserve cette méthode (qui finalement est un alias de "->map()")
    public function any(string $pattern, $handler, $middlewares = null)
    {
        // TODO : il faudrait plutot laissé vide le setMethods([]) comme ca toutes les méthodes sont acceptées !!!!
        return $this->map($pattern, $handler, $middlewares)->setAllowedMethods(['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'PURGE', 'DELETE', 'OPTIONS']);
    }

    /**
     * Add route with multiple methods.
     *
     * @param string                                    $pattern    The route URI pattern
     * @param RequestHandlerInterface|callable|string   $handler    The route callback routine
     * @param string|array|callable|MiddlewareInterface $middleware
     *
     * @return \Chiron\Routing\Route
     */
    // TODO : créer une classe RouteInterface qui servira comme type de retour (il faudra aussi l'ajouter dans le use en début de classe) !!!!!
    // TODO : lever une exception si le type du handler n'est pas correct, par exemple si on lui passe un integer ou un objet non callable !!!!!
    public function map(string $pattern, $handler, $middlewares = null): Route
    {
        if (! isset($middlewares)) {
            $middlewares = [];
        } elseif (! is_array($middlewares)) {
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

        if (! empty($middlewares)) {
            $handler = new RequestHandlerStack($handler);
            foreach ($middlewares as $middleware) {
                $handler->prepend($this->prepareMiddleware($middleware));
            }
        }

        return $this->getRouter()->map($pattern, $handler);
    }

    /**
     * Mounts a collection of callbacks onto a base route.
     *
     * @param string   $baseRoute The route sub pattern to mount the callbacks on
     * @param callable $fn        The callback method
     */
    public function mount(string $prefix, Closure $closure): void
    {
        // Track current base route
        $curBasePath = $this->getRouter()->getBasePath();
        // Build new base route string
        $this->getRouter()->setBasePath($curBasePath . $prefix);
        // Bind the $this var, to app instance.
        $closure = $closure->bindTo($this);
        //$callback = Closure::bind($closure, $this, get_class());
        // TODO : créer un objet RouteGroup avec uniquement les méthode get/post/put...etc pour éviter de passer à la closure tout l'objet 'Application' :(
        //https://github.com/Rareloop/router/blob/master/src/RouteGroup.php     +   https://github.com/Rareloop/router/blob/master/src/Router.php#L176
        // Call the callable
        $closure($this);
        //call_user_func($callback, $group);  // <= corresponse à : call_user_func($closure, $this);
        // TODO : regarder ici pour des arguments à passer au mount : https://github.com/nezamy/route/blob/master/system/Route.php#L185
        //call_user_func_array($closure, $this->bindArgs($this->pramsGroup, $this->matchedArgs));
        // Restore original base route
        $this->getRouter()->setBasePath($curBasePath);
    }


    // $params => string|array
    public function group($params, Closure $closure): void//: RouteGroup
    {
        $group = new RouteGroup($params, $this->getRouter(), $this->getContainer());
        //$closure = $closure->bindTo($group);
        call_user_func($closure, $group);
        // TODO : un return de type $group est à utiliser si on veux ajouter un middleware avec la notation : $app->group(xxxx, xxxxx)->middleware(xxx);
        //return $group;
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
     * @return null|ContainerInterface
     */
    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }

    /**
     * Set container.
     *
     * @param ContainerInterface $container
     *
     * @return Application returns itself to support chaining
     */
    // TODO : voir si on conserve cette méthode ?????
    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;

        return $this;
    }

    /*******************************************************************************
     * Config
     ******************************************************************************/

    public function getConfig(): Config
    {
        return $this->container->get('config');
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
    public function getLogger(): LoggerInterface
    {
        return $this->container->get('logger');

        /*
                // If a logger hasn't been set, use NullLogger
                if (! $this->logger instanceof LoggerInterface) {
                    $this->logger = new NullLogger();
                }

                return $this->logger;*/
    }

    /**
     * Sets logger.
     *
     * @param LoggerInterface $logger
     *
     * @return Application returns itself to support chaining
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->container->set('logger', $logger);

        return $this;
        /*
        $this->logger = $logger;
        return $this;*/
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

    /*******************************************************************************
     * Constructor
     ******************************************************************************/

    /**
     * Instantiate a new Application.
     *
     * Objects and parameters can be passed as argument to the constructor.
     *
     * @param array $values the parameters or objects
     */
    // TODO : lui passer un container en paramétre ?????
    public function __construct(array $settings = [])
    {
        /*
                // TODO : créer plutot une classe "EmptyResponseHandler" qui utilisera une responseFactory pour renvoyer une response vide. et c'est cette classe qu'on passera à la Stack.
                $emptyResponse = new CallableRequestHandlerDecorator(function ($request) {
                    // TODO : passer le charset + version http 1.1 par défaut à cette réponse !!!!
                    //$this->container['charset'] et $this->container['httpVersion']
                    // TODO : regarder ici pour créer un truc vide genre un Home.php => https://github.com/Zegnat/php-website-starter/blob/develop/app/RequestHandler/Home.php
                    $response = new Response(204);

                    return $response;
                });
        */
        $emptyResponse = new FixedResponseHandler(new Response(204));

        $this->requestHandler = new RequestHandlerStack($emptyResponse);

        $this->container = new Container();

        $services = new DefaultServicesProvider();
        $services->register($this->container);

        $this->container->set(Application::class, $this);

//        $this->container['debug'] = false;
//        $this->container['charset'] = 'UTF-8';
//        $this->container['httpVersion'] = '1.1';
//        $this->container['logger'] = null;

        // TODO : il faut s'assurer que le charset est bien propagé dans la création de la response !!!! et mettre en minuscule cette valeur !!!!!
        $this->container['charset'] = 'UTF-8';
        $this->container['httpVersion'] = '1.1';
        $this->container['basePath'] = '';

        foreach ($settings as $key => $value) {
            $this->container[$key] = $value;
        }

        $config = new Config($settings);
        $this->container['config'] = $config;
        //$this->container->config = $config;
        //$this->container->set('config', $config);

        //$router->setBasePath($request->getUri()->getBasePath());
        //$this->basePath = $this->container['basePath'];
        //$this->setBasePath($this->container['basePath']);

        $this->getRouter()->setBasePath($this->container->config['settings.basePath'] ?? '/');

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
     * @throws LogicException if trying to clone an instance of a singleton
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
     * @throws LogicException if trying to unserialize an instance of a singleton
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

    public function run(): ResponseInterface
    {
        //$request = Request::createFromGlobals($_SERVER);
        $request = (new \Chiron\Http\Factory\ServerRequestFactory())->createServerRequestFromArray($_SERVER);

        return $this->process($request);
    }

    public function process(ServerRequestInterface $request): ResponseInterface
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
        //$config = $this->container->config;
        $config = $this->container['config'];

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
     * @return Application
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
}
