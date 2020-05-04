<?php

declare(strict_types=1);

namespace Chiron;

use Chiron\Bootload\Configurator;
use Chiron\Container\SingletonInterface;
use Chiron\Dispatcher\DispatcherInterface;
use Chiron\Http\Http;
use RuntimeException;

/**
 * This constant defines the framework installation directory.
 */
//defined('CHIRON_PATH') or define('CHIRON_PATH', __DIR__);

/**
 * The application framework core.
 */
final class Application
{
    /** @var DispatcherInterface[] */
    private $dispatchers = [];

    /** @var Configurator */
    private $configurator;

    /**
     * @param Configurator $configurator
     */
    public function __construct(Configurator $configurator)
    {
        $this->configurator = $configurator;
    }

    /**
     * Add new dispatcher. This method must only be called before method `run` will be invoked.
     *
     * @param DispatcherInterface $dispatcher
     */
    // TODO : il faudrait gérer le cas ou l'on souhaite ajouter un dispatcher au dessus de la stack. Ajouter un paramétre 'bool $onTop = false' à cette méthode ????
    public function addDispatcher(DispatcherInterface $dispatcher): void
    {
        $this->dispatchers[] = $dispatcher;
    }

    /**
     * Run application and process user requests using selected dispatcher or throw an exception.
     *
     * @throws RuntimeException
     *
     * @return mixed Could be an 'int' for command-line dispatcher or 'void' for web dispatcher.
     */
    public function run()
    {
        $this->configurator->init();

        foreach ($this->dispatchers as $dispatcher) {
            if ($dispatcher->canDispatch()) {
                return $dispatcher->dispatch();
            }
        }

        throw new RuntimeException('Unable to locate active dispatcher.');
    }
}
