<?php

declare(strict_types=1);

namespace Chiron;

use Chiron\Boot\Directories;
use Chiron\Boot\DirectoriesInterface;
use LogicException;

//https://github.com/spiral/boot/blob/master/src/AbstractKernel.php
//https://github.com/spiral/framework/blob/master/src/Framework/Kernel.php
class AbstractKernel
{
    /**
     * @param Container $container
     * @param array     $directories
     */
    public function __construct(Container $container, array $directories)
    {
        $this->container = $container;

        $this->container->bindSingleton(KernelInterface::class, $this);
        $this->container->bindSingleton(self::class, $this);
        $this->container->bindSingleton(static::class, $this);

        $this->container->bindSingleton(
            DirectoriesInterface::class,
            new Directories($this->mapDirectories($directories))
        );

        $this->finalizer = new Finalizer();
        $this->container->bindSingleton(FinalizerInterface::class, $this->finalizer);

        $this->bootloader = new BootloadManager($this->container);
        $this->bootloader->bootload(static::SYSTEM);
    }

    /**
     * Normalizes directory list and adds all required aliases.
     *
     * @param array $directories
     *
     * @return array
     */
    protected function mapDirectories(array $directories): array
    {
        if (! isset($directories['root'])) {
            throw new LogicException("Missing required directory 'root'.");
        }

        if (! isset($directories['app'])) {
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
            'config'    => $directories['app'] . '/config/',
            'resources' => $directories['app'] . '/resources/',
        ], $directories);
    }

    /**
     * Initiate application core.
     *
     * @param array                     $directories  Spiral directories should include root,
     *                                                libraries and application directories.
     * @param EnvironmentInterface|null $environment  Application specific environment if any.
     * @param bool                      $handleErrors Enable global error handling.
     *
     * @return self|static
     */
    public static function init(array $directories, EnvironmentInterface $environment = null, bool $handleErrors = true): ?self
    {
        if ($handleErrors) {
            ExceptionHandler::register();
        }

        $core = new static(new Container(), $directories);

        $core->container->bindSingleton(
            EnvironmentInterface::class,
            $environment ?? new Environment()
        );

        try {
            $core->bootload();
            $core->bootstrap();
        } catch (\Throwable $e) {
            ExceptionHandler::handleException($e);

            return null;
        }

        return $core;
    }

    //https://github.com/drupal/core/blob/0ae6421e8d33e227170ec9819055110057b8f738/lib/Drupal/Core/DrupalKernel.php#L299
    //$app_root = static::guessApplicationRoot();
    /**
     * Determine the application root directory based on this file's location.
     *
     * @return string
     *                The application root.
     */
    /*
    public static function guessApplicationRoot() {
        // Determine the application root by:
        // - Removing the namespace directories from the path.
        // - Getting the path to the directory two levels up from the path
        //   determined in the previous step.
        return dirname(dirname(substr(__DIR__, 0, -strlen(__NAMESPACE__))));
    }*/

    /**
     * Gets the application root dir (path of the project's composer file).
     *
     * @return string The project root dir
     */
    /*
    private $projectDir;

    public function getProjectDir()
    {
        if (null === $this->projectDir) {
            $r = new \ReflectionObject($this);
            $dir = $rootDir = \dirname($r->getFileName());
            while (!file_exists($dir.'/composer.json')) {
                if ($dir === \dirname($dir)) {
                    return $this->projectDir = $rootDir;
                }
                $dir = \dirname($dir);
            }
            $this->projectDir = $dir;
        }
        return $this->projectDir;
    }*/
}
