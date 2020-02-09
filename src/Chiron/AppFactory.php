<?php

declare(strict_types=1);

namespace Chiron;

use Chiron\Config\ConfigInterface;
use Chiron\Config\ConfigManager;
use Chiron\Container\Container;
use Chiron\Http\Emitter\ResponseEmitter;
use Chiron\Pipe\PipelineBuilder;
use Chiron\Provider\ConfigManagerServiceProvider;
use Chiron\Provider\DotEnvServiceProvider;
use Chiron\Provider\ErrorHandlerServiceProvider;
use Chiron\Provider\HttpFactoriesServiceProvider;
use Chiron\Provider\LoggerServiceProvider;
use Chiron\Provider\MiddlewaresServiceProvider;
use Chiron\Provider\RouterServiceProvider;
use Chiron\Provider\ServerRequestCreatorServiceProvider;
use Chiron\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Chiron\Boot\DirectoriesInterface;
use Chiron\Boot\Directories;
use Chiron\Boot\EnvironmentInterface;
use Chiron\Boot\Environment;
use Chiron\Boot\BootloadManager;

//https://github.com/laravel/lumen-framework/blob/5.8/src/Application.php

class AppFactory
{
    /**
     * The base path of the application installation.
     *
     * @var string
     */
    private $basePath;
    private $container;

    /**
     * Container constructor.
     *
     * @param array $definitions
     * @param ServiceProviderInterace[] $providers
     *
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     */
    /*
    public function __construct(
        array $definitions = [],
        array $providers = []
    ) {
        $this->setMultiple($definitions);
        $this->deferredProviders = new SplObjectStorage();
        foreach ($providers as $provider) {
            $this->addProvider($provider);
        }
    }*/

    // TODO : passer cette méthode en static !!!!!
    // TODO : créer une méthode pour alimenter le basepath. du style : public static function setBasePath(string $basePath)
    public function create(string $basePath = null): Kernel
    {
        // TODO : ajouter un rtrim($basepath, '\/') ?????
        $this->basePath = $basePath;

        // TODO : il faudrait binder le $container aux classes ContainerInterface::class et Container::class et surement invokerinterface et factoryinterface
        $this->container = new Container();

        $this->registerBaseServiceProviders();

        // TODO : à déporter dans un serviceprovider cad dans un fichier séparé !!!!
        $directories = [];
        $this->container->share(DirectoriesInterface::class, new Directories($this->mapDirectories($directories)));

        // the container will instanciate the Application class and inject dependencies.
        $kernel = $this->container->get(Kernel::class);

        // TODO : attention si on utilise ce bout de code, il faudra aussi faire une méthode __clone() qui remodifie ces valeurs d'instances. => https://github.com/Wandu/Framework/blob/master/src/Wandu/DI/Container.php#L65
        //$this->instance(Kernel::class, $this);
        //$this->instance(Kernel::class, $this);
        //$this->instance('kernel', $this);

        // TODO : à virer !!!!
        $this->container->share(Kernel::class, $kernel);
        $this->container->alias('kernel', Kernel::class);


        $bootload = new BootloadManager($this->container);

        $bootload->register(DotEnvServiceProvider::class);
        $bootload->register(ConfigManagerServiceProvider::class);

        return $kernel;
    }

    /**
     * Normalizes directory list and adds all required aliases.
     *
     * @param array $directories
     * @return array
     */
    protected function mapDirectories(array $directories): array
    {
        if (!isset($directories['root'])) {
            //throw new LogicException("Missing required directory 'root'.");
            $directories['root'] = $this->basePath();
        }

        if (!isset($directories['app'])) {
            $directories['app'] = $directories['root'] . '/app/';
        }

        return array_merge([
            // public root
            'public'    => $directories['root'] . '/public/',
            // vendor libraries
            'vendor'    => $directories['root'] . '/vendor/',
            // data directories
            'runtime'   => $directories['root'] . '/runtime/',
            'cache'     => $directories['root'] . '/runtime/cache/',
            // application directories
            //'config'    => $directories['app'] . '/config/',
            'config'    => $directories['root'] . '/config/',
            'resources' => $directories['app'] . '/resources/',
        ], $directories);
    }

    /**
     * Register all of the base service providers.
     */
    // TODO : enregistrer aussi les alias : https://github.com/laravel/framework/blob/5.8/src/Illuminate/Foundation/Application.php#L1128
    protected function registerBaseServiceProviders()
    {
        $this->container->register(DotEnvServiceProvider::class);
        $this->container->register(ConfigManagerServiceProvider::class);
        $this->container->register(ServerRequestCreatorServiceProvider::class);
        $this->container->register(HttpFactoriesServiceProvider::class);
        $this->container->register(LoggerServiceProvider::class);
        $this->container->register(RouterServiceProvider::class);
        $this->container->register(MiddlewaresServiceProvider::class);
        $this->container->register(ErrorHandlerServiceProvider::class);
    }

    /**
     * Load a configuration file into the application.
     *
     * @param  string  $name
     * @return void
     */
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
    /**
     * Get the path to the given configuration file.
     *
     * If no name is provided, then we'll return the path to the config folder.
     *
     * @param  string|null  $name
     * @return string
     */
    public function getConfigurationPath($name = null)
    {
        if (! $name) {
            $appConfigDir = $this->basePath('config').'/';
            if (file_exists($appConfigDir)) {
                return $appConfigDir;
            } elseif (file_exists($path = __DIR__.'/../config/')) {
                return $path;
            }
        } else {
            $appConfigPath = $this->basePath('config').'/'.$name.'.php';
            if (file_exists($appConfigPath)) {
                return $appConfigPath;
            } elseif (file_exists($path = __DIR__.'/../config/'.$name.'.php')) {
                return $path;
            }
        }
    }

    /**
     * Get the path to the application's language files.
     *
     * @return string
     */
    protected function getLanguagePath()
    {
        if (is_dir($langPath = $this->basePath().'/resources/lang')) {
            return $langPath;
        } else {
            return __DIR__.'/../resources/lang';
        }
    }

    /**
     * Get the path to the application "app" directory.
     *
     * @return string
     */
    public function path()
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'app';
    }
    /**
     * Get the base path for the application.
     *
     * @param  string|null  $path
     * @return string
     */
    public function basePath($path = null)
    {
        if (isset($this->basePath)) {
            return $this->basePath.($path ? '/'.$path : $path);
        }
        //if ($this->runningInConsole()) {
        //    $this->basePath = getcwd();
        //} else {
            $this->basePath = realpath(getcwd().'/../');
        //}
        return $this->basePath($path);
    }
    /**
     * Get the path to the database directory.
     *
     * @param  string  $path
     * @return string
     */
    public function databasePath($path = '')
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'database'.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Get the path to the resources directory.
     *
     * @param  string|null  $path
     * @return string
     */
    public function resourcePath($path = '')
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'resources'.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Get the storage path for the application.
     *
     * @param  string|null  $path
     * @return string
     */
    public function storagePath($path = '')
    {
        return ($this->storagePath ?: $this->basePath.DIRECTORY_SEPARATOR.'storage').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
    /**
     * Set the storage directory.
     *
     * @param  string  $path
     * @return $this
     */
    public function useStoragePath($path)
    {
        $this->storagePath = $path;
        $this->instance('path.storage', $path);
        return $this;
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
        $this->add('environment', $env);

        return $this;
    }

    /**
     * Get the config object.
     *
     * @return Config Current configuration
     */
    public function getEnvironment(): string
    {
        return $this->get('environment');
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->get('request');
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
        $this->add('config', $config);

        return $this;
    }

    /**
     * Get the config object.
     *
     * @return Config Current configuration
     */
    public function getConfig(): ConfigManager
    {
        return $this->get('config');
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
        $this->add('logger', $logger);

        return $this;
    }

    /**
     * Get the config object.
     *
     * @return Config Current configuration
     */
    public function getLogger(): LoggerInterface
    {
        return $this->get('logger');
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

}
