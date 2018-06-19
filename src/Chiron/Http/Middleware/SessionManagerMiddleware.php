<?php

declare(strict_types = 1);

namespace Chiron\Http\Middleware;

use Chiron\Http\Session\SessionManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionManagerMiddleware implements MiddlewareInterface
{
    /**
     * @var string|null The session name
     */
    private $name;
    /**
     * @var string The attribute name
     */
    private $attribute = 'Chiron::SESSION';

    /**
     * Set the session name.
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }
    /**
     * Set the attribute name to store the sesion instance.
     */
    public function attribute(string $attribute): self
    {
        $this->attribute = $attribute;
        return $this;
    }
    /**
     * Process a server request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = new SessionManager($request->getCookieParams());

        if (! is_null($this->name)) {
            $session->setName($this->name);
        }

        if (! $session->isStarted()) {
            $session->start();
        }

        $request = $request->withAttribute($this->attribute, $session);

        return $handler->handle($request);
    }
}
