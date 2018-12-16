<?php

/**
 * Chiron (http://www.chironframework.com).
 *
 * @see      https://github.com/ncou/Chiron
 *
 * @license   https://github.com/ncou/Chiron/blob/master/licenses/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Chiron\Provider;

use Chiron\Routing\Router;
use Chiron\Routing\RouterInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Chiron\Http\Factory\ResponseFactory;
use Chiron\Routing\Resolver\CallableResolver;
use Chiron\Routing\Strategy\ApplicationStrategy;
use Chiron\KernelInterface;
use Chiron\Config\Config;

/**
 * Config service provider.
 */
class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Register Chiron system services.
     *
     * @param ContainerInterface $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register(KernelInterface $kernel): void
    {
        $settings['app']['settings']['basePath'] = '/';
        $settings['app']['debug'] = false;

        $config = new Config($settings);
        // register config object
        $kernel->set(Config::class, $config);

        // add alias
        $kernel['config'] = function ($c) {
            return $c->get(Config::class);
        };
    }
}
