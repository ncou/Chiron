<?php

declare(strict_types=1);

namespace Chiron;

use Chiron\Config\ConfigInterface;
use Chiron\Config\ConfigManager;
use Chiron\Container\Container;
use Chiron\Http\Emitter\ResponseEmitter;
use Chiron\Http\Emitter\SapiEmitter;
use Chiron\Provider\ConfigManagerServiceProvider;
use Chiron\Provider\DotEnvServiceProvider;
use Chiron\Provider\ErrorHandlerServiceProvider;
use Chiron\Provider\HttpFactoriesServiceProvider;
use Chiron\Provider\LoggerServiceProvider;
use Chiron\Provider\MiddlewaresServiceProvider;
use Chiron\Provider\RouterServiceProvider;
use Chiron\Provider\ServerRequestCreatorServiceProvider;
use Chiron\Routing\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Chiron\Boot\DirectoriesInterface;
use Chiron\Boot\Directories;
use Chiron\Boot\EnvironmentInterface;
use Chiron\Boot\Environment;

//https://github.com/lambirou/babiphp/blob/master/system/Container/ReflectionContainer.php

// TODO : gérer les alias dans le container => https://github.com/laravel/framework/blob/e0dbd6ab143286d81bedf2b34f8820f3d49ea15f/src/Illuminate/Foundation/Application.php#L1076
// TODO : gestion du "call()" qui retrouve automatiquement les paramétres de la fonction par rapport à ce qu'il y a dans le container :
//https://github.com/Wandu/Framework/blob/master/src/Wandu/DI/Container.php#L279
//https://github.com/illuminate/container/blob/master/Container.php#L569    +   https://github.com/laravel/framework/blob/e0dbd6ab143286d81bedf2b34f8820f3d49ea15f/src/Illuminate/Foundation/Application.php#L795

// TODO : ajouter une méthode getEmitter() et setEmitter()
// TODO : renommer la méthode getRequest en getServerRequest()

// TODO : faire un imlplements de l'interface RequestHandlerInterface car il y a la méthode ->handle() qui existe dans cette classe Kernel
class Kernel
{
    private $emitter;
    private $container;

    // TODO : ajouter la possibiilité de passer directement un objet Config dans le constructeur, si il est null on initialise un nouveau config.
    // TODO : passer en paramétre un EmitterInterface ert lier dans le container cette interface avec la classe ResponseEmitter (ou éventuellement SapiEmitter)
    public function __construct(Container $container, ResponseEmitter $emitter)
    {
        $this->emitter = $emitter;
        $this->container = $container;
    }

    /**
     * Register a service provider with the application.
     *
     * @param ServiceProviderInterface|string $provider
     *
     * @return self
     */
    /*
    public function register($provider): self
    {
        $this->container->register($provider);

        return $this;
    }*/


    public function getContainer(): Container
    {
        return $this->container;
    }



    /*
        public function __clone()
        {
            $this->set(Kernel::class, $this);
            $this->set(KernelInterface::class, $this);
            $this->set('kernel', $this);
        }
        */

    // TODO : vérifier que cela ne pose pas de problémes si on passe un content à null, si c'est le cas initialiser ce paramétre avec chaine vide.
    public function createResponse(string $content = null, int $statusCode = 200, array $headers = []): ResponseInterface
    {
        $response = $this->container->get('responseFactory')->createResponse($statusCode);

        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        // create a new body, because in the PSR spec it's not sure the body in writable in the newly created response.
        //$response->getBody()->write($content);
        if (! is_null($content)) {
            // TODO : vérifier si il faut faire un rewind ou non sur le body suite au write !!!!
            $body = $this->container->get('streamFactory')->createStream($content);
            $response = $response->withBody($body);
        }
        //$body = $this->get('streamFactory')->createStreamFromFile('php://temp', 'wb+');
        //$body->write($content);

        return $response;
    }

    /**
     * Get or check the current application environment.
     *
     * @param  mixed
     *
     * @return string
     */
    /*
    //https://github.com/laravel/lumen-framework/blob/5.8/src/Application.php#L162
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
     * Set the environment.
     *
     * @param Config $config
     *
     * @return Kernel
     */
    public function setEnvironment(string $env): self
    {
        $this->container->add('environment', $env);

        return $this;
    }

    /**
     * Get the config object.
     *
     * @return Config Current configuration
     */
    public function getEnvironment(): string
    {
        return $this->container->get('environment');
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->container->get('request');
    }

    /**
     * Set the environment.
     *
     * @param Config $config
     *
     * @return Kernel
     */
    public function setConfig(ConfigManager $config): self
    {
        $this->container->add('config', $config);

        return $this;
    }

    /**
     * Get the config object.
     *
     * @return Config Current configuration
     */
    public function getConfig(): ConfigManager
    {
        return $this->container->get('config');
    }

    /**
     * Set the environment.
     *
     * @param Config $config
     *
     * @return Kernel
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->container->add('logger', $logger);

        return $this;
    }

    /**
     * Get the config object.
     *
     * @return Config Current configuration
     */
    public function getLogger(): LoggerInterface
    {
        return $this->container->get('logger');
    }

    public function setDebug(bool $debug): self
    {
        $settings['app']['debug'] = $debug;
        $this->getConfig()->merge($settings);

        return $this;
    }

    public function getDebug(): bool
    {
        return $this->getConfig()->get('app.debug');
    }

    public function setBasePath(string $basePath): self
    {
        $settings['app']['settings']['basePath'] = $basePath;
        $this->getConfig()->merge($settings);

        return $this;
    }

    public function getBasePath(): string
    {
        return $this->getConfig()->get('app.settings.basePath');
    }

    /*******************************************************************************
     * Router
     ******************************************************************************/

    /**
     * Get the config object.
     *
     * @return RouterInterface
     */
    public function getRouter(): RouterInterface
    {
        return $this->container->get('router');
    }

    public function middleware($middleware): self
    {
        $this->getRouter()->middleware($middleware);

        return $this;
    }

    /*******************************************************************************
     * Run App
     ******************************************************************************/

    public function run(): void
    {
        $this->container->boot();

        $this->getRouter()->setBasePath($this->getConfig()->get('app.settings.basePath') ?? '/');

        $request = $this->getRequest();

        $response = $this->handle($request);

        $this->emit($response);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->getRouter()->handle($request);
    }

    protected function emit(ResponseInterface $response): bool
    {
        return $this->emitter->emit($response);
    }

}
