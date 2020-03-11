<?php

declare(strict_types=1);

namespace Chiron\Bootloader;

use Chiron\Views\TemplateRendererInterface;
use Chiron\Container\Container;
use Chiron\Boot\DirectoriesInterface;
use Chiron\Views\Config\ViewsConfig;
use Chiron\Bootload\BootloaderInterface;
use Chiron\Container\BindingInterface;

class ViewBootloader implements BootloaderInterface
{
    public function boot(TemplateRendererInterface $renderer, ViewsConfig $config): void
    {

/*
        if (! $config->has('templates')) {
            $config->merge(['templates' => [
                'extension' => 'phtml',
                'paths'     => [$dirs->get('templates')],
            ]]);
        }
*/

        // Add template file extension.
        $renderer->setExtension($config->getExtension());

        // add template paths
        foreach ($config->getPaths() as $namespace => $paths) {
            $namespace = is_numeric($namespace) ? null : $namespace;

            foreach ((array) $paths as $path) {
                $renderer->addPath($path, $namespace);
            }
        }
    }
}
