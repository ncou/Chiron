<?php

declare(strict_types=1);

namespace Chiron\Bootloader;

use Chiron\Views\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Chiron\Boot\DirectoriesInterface;
use Chiron\Views\Config\ViewsConfig;
use Chiron\Bootload\BootloaderInterface;
use Chiron\Container\BindingInterface;
use Chiron\Application;
use Chiron\Http\SapiDispatcher;
use Chiron\Console\ConsoleDispatcher;
use Chiron\Console\Console;
use Chiron\Console\Command\Hello;
use Chiron\Console\Command\Package;
use Chiron\Console\Config\ConsoleConfig;

class CommandBootloader implements BootloaderInterface
{
    public function boot(Console $console, ConsoleConfig $config): void
    {
        foreach ($config->getCommands() as $command) {
            $console->addCommand($command);
        }
    }
}
