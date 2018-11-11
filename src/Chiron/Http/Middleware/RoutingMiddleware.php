<?php

declare(strict_types=1);

namespace Chiron\Http\Middleware;

// TODO : example : https://github.com/zendframework/zend-expressive-router/blob/master/src/Middleware/RouteMiddleware.php
// TODO : regarder ici https://github.com/zrecore/Spark/blob/master/src/Handler/RouteHandler.php    et https://github.com/equip/framework/blob/master/src/Handler/DispatchHandler.php

//namespace Middlewares;

use Chiron\Http\Exception\Client\MethodNotAllowedHttpException;
use Chiron\Http\Exception\Client\NotFoundHttpException;
use Chiron\Http\Psr\Response;
use Chiron\Http\Psr\Stream;
use Chiron\Routing\Route;
use Chiron\Routing\Router;
use Chiron\Routing\RouteResult;
use FastRoute\RouteCollector as FastRouteCollector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RoutingMiddleware implements MiddlewareInterface
{
    // TODO : ajouter une phpdoc avec le type de cette variable (\RouterInterface)
    private $router;

    private $routes = [];

    // TODO : ajouter un RouterInterface comme type hinting pour la paramétre $router
    public function __construct(Router $router)
    {
        $this->router = $router;
        /*
        $this->router = new FastRouteCollector(
            new \FastRoute\RouteParser\Std, new \FastRoute\DataGenerator\GroupCountBased
        );*/
    }

    /**
     * Process a server request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // TODO : il faudrait peut etre récupérer la réponse via un $handle->handle() pour récupérer les headers de la réponse + le charset et version 1.1/1.0 pour le passer dans les exceptions (notfound+methodnotallowed) car on va recréer une nouvelle response !!!! donc si ca se trouve les headers custom genre X-Powered ou CORS vont être perdus lorsqu'on va afficher les message custom pour l'exception 404 par exemple !!!!

        //$result = $this->getDispatchResult($request);
        $result = $this->router->match($request);

        if ($result->isMethodFailure()) {
            $allowedMethods = $result->getAllowedMethods();
            // The OPTIONS request should send back a response with some headers like "Allow" header.
            // TODO : vérifier le comportement avec CORS.
            if ($request->getMethod() === 'OPTIONS') {
                //array_unshift($allowedMethods, 'OPTIONS');
                // TODO : passer un responseFactory en paramétre de ce middleware !!!!!
                return (new Response())->withHeader('Allow', implode(', ', array_merge(['OPTIONS'], $allowedMethods)));
            }

            throw new MethodNotAllowedHttpException($allowedMethods);
        } elseif ($result->isFailure()) {
            // Http error 404 not found
            throw new NotFoundHttpException();
        }

        // add some usefull information about the url used for the routing
        // TODO : faire plutot porter ces informations (method et uri utilisé) directement dans l'objet RouteResult ??????
        //$request = $request->withAttribute('routeInfo', [$request->getMethod(), (string) $request->getUri()]);

        // TODO : bout de code permettant d'injecter les attributs dans la session, à déplacer dans la classe de strategie pour l'invocation des routes !!!!!
        // Inject individual matched parameters.
        foreach ($result->getMatchedParams() as $param => $value) {
            $request = $request->withAttribute($param, $value);
            //$request = $request->withAttribute($param, urldecode($value)); // TODO : regarder si un urldecode est nécessaire.
        }
        // Inject the actual route result in the request
        $request = $request->withAttribute(RouteResult::class, $result);

        // Execute the next handler
        $response = $handler->handle($request);
        // As per RFC, HEAD request can't have a body.
        // TODO : déplacer ce bout de code dans le EmitterMiddleware ???? ATTENTION : bien vérifier ou se trouve le contentLengthMiddleware car il va devoir recalculer le header "Content-Length" à 0, suite au Body qui vient d'être supprimé !!!!
        if ($request->getMethod() === 'HEAD') {
            $response = $response->withBody(new Stream(fopen('php://temp', 'wb+')));
        }

        return $response;
    }
}
