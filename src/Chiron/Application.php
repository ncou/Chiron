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
use Chiron\Http\Emitter\ResponseEmitter;
use Chiron\Http\Psr\Response;
use Chiron\Pipe\Decorator\FixedResponseMiddleware;
use Chiron\Pipe\Pipeline;
use Chiron\Routing\Route;
use Chiron\Routing\Router;
use Chiron\Routing\RouterInterface;
use Chiron\Routing\Strategy\JsonStrategy;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Application
{
    private const VERSION = '1.0.0-alpha';

    /* @var ResponseEmitter */
    private $emitter;

    /**
     * The router instance.
     *
     * @var RouterInterface // TODO : interface à créer !!!!
     */
    //private $router;

    private $pipeline;

    /**
     * The kernel (container).
     * Visibility is public for easier access "$app->kernel->register(xxx)".
     *
     * @var KernelInterface
     */
    public $kernel;

    /**
     * The Router instance.
     * Visibility is public for easier access "$app->router->any('xxx')".
     *
     * @var \Chiron\Routing\Router
     */
    public $router;

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
    // TODO : permettre de passer en paramétre un kernel null.
    public function __construct(KernelInterface $kernel)
    {
        /*
        if (is_null($kernel)) {
            $kernel = new Kernel();
        }*/
        $this->kernel = $kernel->boot();
        $this->pipeline = new Pipeline($this->kernel);
        $this->emitter = new ResponseEmitter();
        $this->router = $this->kernel->getRouter();

        // TODO : réfléchir à utiliser ce bout de code => https://github.com/cakephp/cakephp/blob/master/src/Core/Configure.php#L99
        //ini_set('display_errors', $kernel['debug'] ? '1' : '0');
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

    public function run(): void
    {
        $request = $this->kernel->get('request');

        $response = $this->handle($request);

        $this->emit($response);
    }

    // TODO : renommer cette fonction en "handleRequest()"
    public function handle(ServerRequestInterface $request): ResponseInterface
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

        $responseFactory = $this->kernel->get(ResponseFactoryInterface::class);
        $emptyResponse = $responseFactory->createResponse(204);

        $emptyResponse = new FixedResponseMiddleware($emptyResponse);

        // add an empty response as default response if no route found and no 404 handler is added.
        //array_push($this->middlewares, $emptyResponse);
        $this->router->middleware($emptyResponse);

        $response = $this->pipeline->pipe($this->router->getMiddlewareStack())->handle($request);

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

    public function emit(ResponseInterface $response): bool
    {
        return $this->emitter->emit($response);
    }

    /**
     * Register a service provider with the application.
     *
     * @param ServiceProviderInterface|string $provider
     */
    public function register($provider): self
    {
        $this->kernel->register($provider);

        return $this;
    }

    public function middleware($middleware): self
    {
        $this->router->middleware($middleware);

        return $this;
    }

    /**
     * Get or check the current application environment.
     *
     * @param  mixed
     *
     * @return bool|string
     */
    // TODO : utiliser plutot cette méthode : https://github.com/solid-layer/framework/blob/59f39fac2094598918731b107ba0b9298bab6394/src/Clarity/Kernel/Kernel.php#L64
    /*
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
    }*/

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version(): string
    {
        return self::VERSION;
    }
}
