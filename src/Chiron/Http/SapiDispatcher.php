<?php

declare(strict_types=1);

namespace Chiron\Http;

use Chiron\Http\Emitter\ResponseEmitter;
use Nyholm\Psr7Server\ServerRequestCreatorInterface;
use Psr\Http\Message\ServerRequestInterface;

class SapiDispatcher implements DispatcherInterface
{
    /** @var ServerRequestInterface */
    private $request;

    /** @var Http */
    private $http;

    /** @var ResponseEmitter */
    private $emitter;

    // TODO : utiliser plutot un EmiterInterface au lieu de l'objet ResponseEmitter !!!!
    public function __construct(ServerRequestCreatorInterface $requestCreator, Http $http, ResponseEmitter $emitter)
    {
        $this->request = $requestCreator->fromGlobals();
        $this->http = $http;
        $this->emitter = $emitter;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(): void
    {
        // TODO : il faudrait éventuellement mettre un try/catch autour du handle et du emit dans le cas ou le Middleware ErrorHandler n'est pas ajouté manuellement par l'utilisateur. Dans ce cas on afficherait l'erreur directement en HTML.  exemple :     https://github.com/spiral/framework/blob/master/src/Http/SapiDispatcher.php#L69
        $response = $this->http->handle($this->request);

        $this->emitter->emit($response);
    }

    /**
     * {@inheritdoc}
     */
    public function canDispatch(): bool
    {
        return php_sapi_name() !== 'cli';
    }
}
