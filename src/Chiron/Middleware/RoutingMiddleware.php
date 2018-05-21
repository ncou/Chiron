<?php

declare(strict_types=1);

namespace Chiron\Middleware;

// TODO : example : https://github.com/zendframework/zend-expressive-router/blob/master/src/Middleware/RouteMiddleware.php
// TODO : regarder ici https://github.com/zrecore/Spark/blob/master/src/Handler/RouteHandler.php    et https://github.com/equip/framework/blob/master/src/Handler/DispatchHandler.php

//namespace Middlewares;

use Chiron\Http\Exception\MethodNotAllowedHttpException;
use Chiron\Http\Exception\NotFoundHttpException;
use Chiron\Http\NullStream;
use Chiron\Http\Response;
use Chiron\Routing\Router;
use Chiron\Routing\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RoutingMiddleware implements MiddlewareInterface
{
    // TODO : ajouter une phpdoc avec le type de cette variable (\RouterInterface)
    private $router;

    // TODO : ajouter un RouterInterface comme type hinting pour la paramétre $router
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Process a server request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // TODO : il faudrait peut etre récupérer la réponse via un $handle->handle() pour récupérer les headers de la réponse + le charset et version 1.1/1.0 pour le passer dans les exceptions (notfound+methodnotallowed) car on va recréer une nouvelle response !!!! donc si ca se trouve les headers custom genre X-Powered ou CORS vont être perdus lorsqu'on va afficher les message custom pour l'exception 404 par exemple !!!!

        $matched = $this->router->match($request);

        if ($matched->isMethodFailure()) {
            $allowedMethods = $matched->getAllowedMethods();
            // The OPTIONS request should send back a response with some headers like "Allow" header.
            // TODO : vérifier le comportement avec CORS.
            if ($request->getMethod() === 'OPTIONS') {
                //array_unshift($allowedMethods, 'OPTIONS');
                return (new Response())->withHeader('Allow', implode(', ', array_merge(['OPTIONS'], $allowedMethods)));
            }

            throw new MethodNotAllowedHttpException($allowedMethods);
        } elseif ($matched->isFailure()) {
            throw new NotFoundHttpException();
        }

        // add some usefull information about the url used for the routing
        // TODO : faire plutot porter ces informations (method et uri utilisé) directement dans l'objet RouteResult ??????
        //$request = $request->withAttribute('routeInfo', [$request->getMethod(), (string) $request->getUri()]);

        // Inject individual matched parameters.
        foreach ($matched->getMatchedParams() as $param => $value) {
            $request = $request->withAttribute($param, $value);
        }
        // Inject the actual route result in the request
        $request = $request->withAttribute(RouteResult::class, $matched);
        // Execute the next handler
        $response = $handler->handle($request);
        // As per RFC, HEAD request can't have a body.
        // TODO : déplacer ce bout de code dans le EmitterMiddleware ????
        if ($request->getMethod() === 'HEAD') {
            $response = $response->withBody(new NullStream());
        }

        return $response;
    }
}
