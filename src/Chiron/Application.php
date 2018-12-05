<?php

declare(strict_types=1);

//***********************
// Enregistrer automatiquement les services => cad un package discovery => https://medium.com/@taylorotwell/package-auto-discovery-in-laravel-5-5-ea9e3ab20518
// https://github.com/laravel/framework/blob/master/src/Illuminate/Foundation/PackageManifest.php
// Il y a aussi ce package :     https://github.com/appzcoder/laravel-package-discovery
// Package installer auto avec ZEND : https://github.com/zendframework/zend-component-installer
// Yii2 => https://github.com/yiisoft/yii2-composer/blob/master/Installer.php
//***********************

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
use Chiron\Pipe\Decorator\FixedResponseHandler;
use Chiron\Pipe\Decorator\FixedResponseMiddleware;
use Chiron\Pipe\Pipeline;
use Chiron\Http\Psr\Response;
use Chiron\Http\Response\EmptyResponse;
use Chiron\Http\ResponseEmitter;
use Chiron\Http\ServerRequestCreator;
use Chiron\Provider\ApplicationServiceProvider;
use Chiron\Provider\ErrorHandlerServiceProvider;
use Chiron\Provider\HttpFactoriesServiceProvider;
use Chiron\Provider\MiddlewaresServiceProvider;
use Chiron\Provider\ServerRequestCreatorServiceProvider;
use Chiron\Routing\Route;
use Chiron\Routing\RouteGroup;
use Chiron\Routing\Router;
use Chiron\Routing\RouterInterface;
use Chiron\Routing\RouteCollectionTrait;
use Chiron\Routing\MiddlewareAwareTrait;
use Chiron\Routing\RouteCollectionInterface;
use Chiron\Routing\MiddlewareAwareInterface;
use Chiron\Routing\Strategy\StrategyAwareInterface;
use Chiron\Routing\Strategy\CallableResolver;
use Chiron\Routing\Strategy\ApplicationStrategy;
use Chiron\Routing\Strategy\JsonStrategy;
use Closure;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Chiron\Http\Factory\ResponseFactory;

class Application implements RouteCollectionInterface, MiddlewareAwareInterface
{
    // TODO : à virer et ajouter un $router en variable public de classe.
    use RouteCollectionTrait;
    use MiddlewareAwareTrait;

    private const VERSION = '1.0.0-alpha';

    /* @var ResponseEmitter */
    private $emitter;

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
    //private $container;

    /**
     * The router instance.
     *
     * @var RouterInterface // TODO : interface à créer !!!!
     */
    //private $router;

    private $pipeline;

    /**
     * The kernel (container).
     * Visibility is public for easier access "$app->kernel->register(xxx)"
     *
     * @var KernelInterface
     */
    public $kernel;

    /**
     * The Router instance.
     *
     * @var \Chiron\Routing\Router
     */
    //public $router;

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
    public function __construct(KernelInterface $kernel)
    {

        $this->kernel = $kernel->boot();
        $this->pipeline = new Pipeline($this->kernel);
        $this->emitter = new ResponseEmitter();
    }

    // TODO : méthode à virer !!!!!
    public function __construct2(array $settings = [])
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

        $this->container = Container::getInstance(); //new Container();

        //$emptyResponse = new FixedResponseHandler(new EmptyResponse());

        $this->pipeline = new Pipeline($this->container);


        $this->container->set(self::class, $this);

//        $this->container['debug'] = false;
        // TODO : il faut s'assurer que le charset est bien propagé dans la création de la response !!!! et mettre en minuscule cette valeur !!!!!
//        $this->container['charset'] = 'UTF-8';
//        $this->container['httpVersion'] = '1.1';
//        $this->container['logger'] = null;





        // TODO : lever une exception si il manque le basePath => https://github.com/yiisoft/yii2/blob/master/framework/base/Application.php#L222
        $settings['app']['settings']['basePath'] = '/nano5/public';
        $settings['app']['debug'] = true;

        // TODO : utiliser plutot un bout de code genre : on prend d'abord la valeur de APP_DEBUG si elle n'est pas présente on regarde dans le tableau de config passé en paramétre, si toujours pas présent on initialise à false.
        // un truc du genre  : getEnv('APP_DEBUG') ? getEnv('APP_DEBUG') : $this->container['debug'] ? $this->container['debug'] : false
        //$this->container['debug'] = false;

        foreach ($settings as $key => $value) {
            $this->container[$key] = $value;
        }
        //TODO : faire une méthode pour vérifier que les champs obligatoires de l'application sont bien présents, sinon on léve une erreur : https://github.com/yiisoft/yii2/blob/master/framework/base/Application.php#L217

        $config = new Config($settings);
        $this->container['config'] = $config;
        //$this->container->config = $config;
        //$this->container->set('config', $config);

        //$router->setBasePath($request->getUri()->getBasePath());
        //$this->basePath = $this->container['basePath'];
        //$this->setBasePath($this->container['basePath']);

/*
        $router = $this->getRouter();
        // TODO : paramétrage à remonter dans la classe Provider lors de l'instanciation du Router
        $router->setBasePath($this->container->config['settings.basePath'] ?? '/');
        // TODO : paramétrage à remonter dans la classe Provider lors de l'instanciation du Router
        if ($router instanceof StrategyAwareInterface) {
            $router->setStrategy(new ApplicationStrategy(new ResponseFactory(), new CallableResolver($this->container)));
        }
*/
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

        $this->emitter = new ResponseEmitter();
    }

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

    //***************************************************
    //************ ROUTER *******************************
    //***************************************************
    /**
     * Add route with multiple methods.
     *
     * @param string                                  $pattern The route URI pattern
     * @param RequestHandlerInterface|callable|string $handler The route callback routine
     *
     * @return \Chiron\Routing\Route
     */
    // TODO : créer une classe RouteInterface qui servira comme type de retour (il faudra aussi l'ajouter dans le use en début de classe) !!!!!
    // TODO : lever une exception si le type du handler n'est pas correct, par exemple si on lui passe un integer ou un objet non callable !!!!!
    public function map(string $pattern, $handler): Route
    {
        //return $this->getRouter()->map($pattern, $handler);
        return $this->getRouter()->map($pattern, $handler);
    }

    // $params => string|array
    // TODO : renommer $closure en $group
    // TODO : vérifier si on utilise Closure ou callable pour le typehint
    public function group(string $prefix, callable $closure): RouteGroup
    {
        /*
        $group = new RouteGroup($prefix, $this->getRouter(), $this->getContainer());
        // TODO : on fait un bind du this avec le group ????
        //$closure = $closure->bindTo($group);
        call_user_func($closure, $group);
        // TODO : un return de type $group est à utiliser si on veux ajouter un middleware avec la notation : $app->group(xxxx, xxxxx)->middleware(xxx);
        return $group;
        */
        return $this->getRouter()->group($prefix, $closure);
    }

    // TODO : ajouter des méthodes proxy pour : getRoutes / getNamedRoute / hasRoute ?????? voir même pour generateUri et getBasePath/setBasePath ??????

    // TODO : ajouter une interface pour le router, et faire en sorte que cette méthode ait un type de retour du genre "RouterInterface", et on pourra aussi créer une méthode "setRouter(RouterInterface $router)"
    public function getRouter(): RouterInterface
    {
        //return $this->router;
        return $this->kernel->get(RouterInterface::class);
    }

    /*
    // TODO : méthode à implémenter !!!!!!
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
        return $this;
    }*/

    //*****************************************************

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

    /*******************************************************************************
     * Container
     ******************************************************************************/
    // TODO : créer un ContainerAwareTrait + une interface

    /**
     * Get container.
     *
     * @return null|ContainerInterface
     */
    /*
    public function getContainer(): ?ContainerInterface
    {
        return $this->kernel->getContainer();
    }*/

    /**
     * Set container.
     *
     * @param ContainerInterface $container
     *
     * @return Application returns itself to support chaining
     */
    // TODO : voir si on conserve cette méthode ?????
    /*
    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;

        return $this;
    }*/

    /*******************************************************************************
     * Config
     ******************************************************************************/

    public function getConfig(): Config
    {
        return $this->kernel->get('config');
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
        return $this->kernel->get('logger');

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
        $this->kernel->set('logger', $logger);

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
        $response = $this->handle();

        $this->emit($response);

        return $response;
    }

    // TODO : renommer cette fonction en "handleRequest()"
    public function handle(ServerRequestInterface $request = null): ResponseInterface
    {
        //die(var_dump($this->getRouter()->getRoutes()));
        //die(var_dump($this->getRouter()->relativePathFor('test111', ['id' => '0123456'])));

        //die(var_dump($request->getServerParam('HTTP_HOST')));

        // apply PHP config settings.
        //$this->boot();

        // TODO : c'est plutot ici qu'on devrait la request::fromGlobals et éventuellement la mettre dans le container, non ????, avec éventuellement la possibilité de lui passer une request en paramétre de la méthode Run($request) et donc on créé la request ou on utilisera celle passée en paramétre. Celka peut servir si un utilisateur créé sa request avant (dans le cas ou il utilise du PSR7 request), mais aussi servir pour nos tests PHPUNIT avec une request pré-initilalisée. Cela peut aussi permettre à l'utilisateur de modifier la request, genre pour un sanitize, ou alors pour changer le type de méthode si il y a un champ formulaire hidden _method pour faire un override de la méthode http.

        // create response
        /*
        $headers = new Headers(['Content-Type' => 'text/html; charset=UTF-8']);
        $httpVersion = $this->getSetting('httpVersion');
        $response = new Response(200, $headers);
        $response = $response->withProtocolVersion($httpVersion);
        */

        if (is_null($request)){
            //$request = (new \Chiron\Http\Factory\ServerRequestFactory())->createServerRequestFromArray($_SERVER);
            $requestCreator = $this->kernel->get(ServerRequestCreator::class);
            $request = $requestCreator->fromGlobals();
        }


        $responseFactory = $this->kernel->get(ResponseFactoryInterface::class);
        $emptyResponse = $responseFactory->createResponse(204);

        $emptyResponse = new FixedResponseMiddleware($emptyResponse);

        // add an empty response as default response if no route found and no 404 handler is added.
        array_push($this->middlewares, $emptyResponse);

        $response = $this->pipeline->pipe($this->middlewares)->handle($request);

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

    public function emit(ResponseInterface $response): void
    {
        $this->emitter->emit($response);
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

    /**
     * Register a service provider with the application.
     *
     * @param  \Illuminate\Support\ServiceProvider|string  $provider
     * @param  bool   $force
     * @return \Illuminate\Support\ServiceProvider
     */
    public function register($provider, $force = false)
    {
        $this->kernel->register($provider, $force);
    }


    /**
     * Get the version number of the application.
     *
     * @return string
     */
    /*
    public function version()
    {
        return 'Chiron Framework version ' . self::VERSION;
    }*/

    /**
     * Determine if the application is running in the console.
     *
     * @return bool
     */
    /*
    public function runningInConsole()
    {
        return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
    }*/

    /**
     * Get or check the current application environment.
     *
     * @param  mixed
     * @return bool|string
     */
    // TODO : utiliser plutot cette méthode : https://github.com/solid-layer/framework/blob/59f39fac2094598918731b107ba0b9298bab6394/src/Clarity/Kernel/Kernel.php#L64
    public function environment()
    {
        $env = env('APP_ENV', config('app.env', 'production'));
        if (func_num_args() > 0) {
            $patterns = is_array(func_get_arg(0)) ? func_get_arg(0) : func_get_args();
            foreach ($patterns as $pattern) {
                if (Str::is($pattern, $env)) {
                    return true;
                }
            }
            return false;
        }
        return $env;
    }

    /**
     * The configured environment.
     *
     * @var string
     */
    private $env;


    /**
     * Set the environment.
     *
     * @param string $env
     * @return \Clarity\Kernel\Kernel
     */
    public function setEnvironment(string $env): self
    {
        $this->env = $env; // TODO : faire un strtolower sur la chaine ?????

        return $this;
    }
    /**
     * Get the environment.
     *
     * @return string Current environment
     */
    // TODO : créer une méthode isEnvironment() ?????
    public function getEnvironment(): string
    {
        return $this->env;
    }

    /**
     * The path provided.
     *
     * @var mixed
     */
    private $paths;
    /**
     * Set the paths.
     *
     * @param mixed $paths
     * @return \Clarity\Kernel\Kernel
     */
    public function setPaths($paths)
    {
        $this->paths = $paths;

        return $this;
    }

    /**
     * Load the configurations.
     *
     * @return \Clarity\Kernel\Kernel
     */
    // TODO : regarder ici comment c'est fait => https://github.com/laravel/framework/blob/master/src/Illuminate/Foundation/Bootstrap/LoadConfiguration.php#L20
    public function loadConfig() : self
    {
        # let's create an empty config with just an empty
        # array, this is just for us to prepare the config
        $this->container->set('config', function () {
            return new Config([]);
        });

        //die(var_dump($this->container));
        //die(var_dump(\Chiron\Container\Container::getInstance()));

        # get the paths and merge the array values to the
        # empty config as we instantiated above
        config(['paths' => $this->paths]);
        # now merge the assigned environment
        config(['environment' => $this->getEnvironment()]);
        # iterate all the base config files and require
        # the files to return an array values
        $base_config_files = iterate_require(
            folder_files($this->paths['config'])
        );
        # iterate all the environment config files and
        # process the same thing as the base config files
        $env_config_files = iterate_require(
            folder_files(
                url_trimmer(
                    $this->paths['config'].'/'.$this->getEnvironment()
                )
            )
        );
        // Merge the base config files and the environment config files as one in the our DI 'config'.

        //dump_d($base_config_files);

        config($base_config_files);
        config($env_config_files);

        return $this;
    }
    /**
     * Load the project timezone.
     *
     * @return \Clarity\Kernel\Kernel
     */
    public function loadTimeZone() : self
    {
        //date_default_timezone_set(config()->app->timezone);

        return $this;
    }

    // TODO : à améliorer et priorisé aussi certains services =>
    public function loadServices() : self
    {
        // TODO : mettre les servicesProvider dans un tableau et faire une boucle pour enregistrer chaque classe. Et mettre cela dans une méthode nommée bootServices()
        // Register ServerRequest creator services
        $serverRequestCreatorService = new ServerRequestCreatorServiceProvider();
        $serverRequestCreatorService->register($this->container);

        // Register HTTP Factories services
        $httpFactoriesService = new HttpFactoriesServiceProvider();
        $httpFactoriesService->register($this->container);

        // Register Application services
        $applicationServices = new ApplicationServiceProvider();
        $applicationServices->register($this->container);

        // Register Middlewares services
        $middlewaresServices = new MiddlewaresServiceProvider();
        $middlewaresServices->register($this->container);

        // Register Error Handler services
        $errorHandlerService = new ErrorHandlerServiceProvider();
        $errorHandlerService->register($this->container);

        return $this;
    }

    /**
     * Provide the most prioritized service providers to be loaded internally, before
     * user's manual providers.
     *
     * @return array
     */
    protected function prioritizedProviders()
    {
        return [];
    }
    /**
     * Load the providers.
     *
     * @param  bool $after_module If you want to load services after calling
     *                               run() function
     * @return \Clarity\Kernel\Kernel
     */
    public function loadServices2($after_module = false,array $services = []): self
    {
        # load all the service providers, providing our
        # native phalcon classes
        $container = new Container;
        $container->setDI($this->di);
        if (empty($services)) {
            $services = config('app.services')->toArray();
        }
        $services = array_merge($this->prioritizedProviders(), $services);
        foreach ($services as $service) {
            $instance = new $service;
            $instance->setDI($this->di);
            if ($instance->isAfterModule() === $after_module) {
                $container->addServiceProvider($instance);
            }
        }
        $container->handle();

        return $this;
    }


    public function getVersion(): string
    {
        return seff::VERSION;
    }


}
