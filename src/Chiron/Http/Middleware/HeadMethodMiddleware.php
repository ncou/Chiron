<?php

declare(strict_types=1);

namespace Chiron\Http\Middleware;

// TODO : example : https://github.com/zendframework/zend-expressive-router/blob/master/src/Middleware/RouteMiddleware.php
// TODO : regarder ici https://github.com/zrecore/Spark/blob/master/src/Handler/RouteHandler.php    et https://github.com/equip/framework/blob/master/src/Handler/DispatchHandler.php

//namespace Middlewares;

//use Chiron\Http\Psr\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HeadMethodMiddleware implements MiddlewareInterface
{
    /** @var StreamFactoryInterface */
    private $streamFactory;

    // TODO : passer en paramétre une responsefactory et un streamfactory.
    public function __construct(StreamFactoryInterface $streamFactory)
    {
        $this->streamFactory = $streamFactory;
    }

    /**
     * Process a server request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Execute the next handler
        $response = $handler->handle($request);

        // As per RFC, HEAD request can't have a body.
        if (strtoupper($request->getMethod()) === 'HEAD') {
            // TODO : il faudrait surement enlever le ContentType et le Content-Lenght ? non ????
            $response = $response->withBody($this->streamFactory->createStream());
        }

        return $response;
    }
}
