<?php

declare(strict_types=1);

namespace Chiron;

use Chiron\Config\ConfigInterface;
use Chiron\Config\ConfigManager;
use Chiron\Container\Container;
use Chiron\Http\Emitter\ResponseEmitter;
use Chiron\Http\Emitter\SapiEmitter;
use Chiron\Http\Http;
use Chiron\Http\DispatcherInterface;
use Chiron\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Chiron\Boot\DirectoriesInterface;
use Chiron\Boot\Directories;
use Chiron\Boot\EnvironmentInterface;
use Chiron\Boot\Environment;
use Nyholm\Psr7Server\ServerRequestCreatorInterface;
use RuntimeException;
use Chiron\Container\SingletonInterface;

final class Application //implements SingletonInterface
{

    /** @var DispatcherInterface[] */
    private $dispatchers = [];

    /**
     * Add new dispatcher. This method must only be called before method `serve`
     * will be invoked.
     *
     * @param DispatcherInterface $dispatcher
     */
    public function addDispatcher(DispatcherInterface $dispatcher): void
    {
        $this->dispatchers[] = $dispatcher;
    }

    /**
     * Start application and serve user requests using selected dispatcher or throw
     * an exception.
     *
     * @return mixed (int for console dispatcher and void for sapi dispatcher)
     * @throws RuntimeException
     */
    // TODO : renommer la méthode en "serve()" ????
    public function run()
    {
        // TODO : utiliser un objet Invoker::class pour executer la méthode dispatch sur l'objet $this->dispatcher pour pouvoir résoudre les potentiels paramétres (facultatif ou non !!!!) ???? ou non ????? exemple :
        //              https://github.com/spiral/framework/blob/master/src/Http/SapiDispatcher.php#L54
        //              https://github.com/spiral/framework/blob/master/src/Console/ConsoleDispatcher.php#L69
        //return $this->dispatcher->dispatch();

        //echo 'TOTO';


        foreach ($this->dispatchers as $dispatcher) {
            if ($dispatcher->canDispatch()) {
                return $dispatcher->dispatch();
            }
        }

        throw new RuntimeException('Unable to locate active dispatcher.');
    }
}
