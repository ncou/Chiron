<?php

declare(strict_types=1);

namespace Chiron\Bootloader;

use Chiron\Bootload\AbstractBootloader;
use Chiron\Views\Config\ViewsConfig;
use Chiron\Views\TemplateRendererInterface;

final class ViewBootloader extends AbstractBootloader
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
            // TODO : créer une constante EMPTY_NAMESPACE dans la classe TemplateRendrerInterface ??? ca serai plus propre que d'utiliser directement "null" dans le code ci dessous !!!
            $namespace = is_int($namespace) ? null : $namespace;

            foreach ((array) $paths as $path) {
                $renderer->addPath($path, $namespace);
            }
        }
    }
}
