<?php

declare(strict_types=1);

namespace Chiron\Bootloader;

use Chiron\Application;
use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Dispatcher\ConsoleDispatcher;

// TODO : utilité de cette classe, pourquoi ne pas faire directement un addDispatcher() dans le méhode Application::init() !!!!
final class ConsoleDispatcherBootloader extends AbstractBootloader
{
    public function boot(Application $application): void
    {
        $application->addDispatcher(resolve(ConsoleDispatcher::class));
    }
}
