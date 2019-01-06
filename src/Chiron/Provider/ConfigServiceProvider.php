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

use Chiron\Config\Config;
use Chiron\KernelInterface;
use Psr\Container\ContainerInterface;

// TODO : créer une classe pour fabriquer l'application, et notamment pour injecter les routes et les middlewares si ils sont indiqués sous forme de texte dans la config => https://github.com/zendframework/zend-expressive/blob/85e2f607109ed8608f4004e622b2aad3bcaa8a4d/src/Container/ApplicationConfigInjectionDelegator.php

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
