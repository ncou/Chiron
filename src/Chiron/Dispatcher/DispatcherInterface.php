<?php

declare(strict_types=1);

namespace Chiron\Dispatcher;

interface DispatcherInterface
{
    /**
     * Dispatch the command (could be a console command or a route command).
     *
     * @return mixed The return value could be an int for the console dispatcher or void for the http dispatcher
     */
    public function dispatch();

    /**
     * Check if the dispacher can work for the current context.
     *
     * @return bool
     */
    public function canDispatch(): bool;
}
