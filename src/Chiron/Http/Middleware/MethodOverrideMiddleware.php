<?php

declare(strict_types=1);

namespace Chiron\Http\Middleware;

//github.com/middlewares/method-override/blob/master/src/MethodOverride.php
//https://github.com/rstgroup/http-method-override
//https://github.com/koolkode/http-komponent/blob/04106b00c0106f4838b1bee138c0f58d6a5b1a25/src/Filter/MethodOverrideFilter.php

//https://github.com/geggleto/method-override/blob/master/src/MethodOverrideMiddleware.php
//https://github.com/phapi/middleware-method-override/blob/master/src/Phapi/Middleware/MethodOverride/MethodOverride.php
//https://github.com/slimphp/Slim/blob/4.x/Slim/Middleware/MethodOverrideMiddleware.php
//https://github.com/middlewares/method-override/blob/master/src/MethodOverride.php
//https://github.com/yiisoft/yii2/blob/master/framework/web/Request.php#L369

//https://github.com/koolkode/http-komponent/blob/master/src/Filter/MethodOverrideFilter.php
//https://github.com/rstgroup/http-method-override

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware HTTP Method Override.
 */
class MethodOverrideMiddleware implements MiddlewareInterface
{
    /**
     * Handle the middleware pipeline call. This calls the next middleware
     * in the queue and after the rest of the middleware pipeline is done
     * the response will be sent to the client.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param callable          $next
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // TODO : on devrait plutot utiliser un truc genre : strtoupper($request->getHeaderLine("X-Http-Method-Override"));
        if ($request->hasHeader('X-Http-Method-Override')) {
            if (! empty($request->getHeader('X-Http-Method-Override')[0])) {
                $request = $request->withMethod($request->getHeader('X-Http-Method-Override')[0]);
            }
        }
        if (strtoupper($request->getMethod()) == 'GET') {
            if (! empty($request->getQueryParams()['_method'])) {
                $method = $request->getQueryParams()['_method'];
                $request = $request->withMethod($method);
            }
        }
        if ($request->getMethod() == 'POST') {
            if (! empty($request->getParsedBody()['_method'])) {
                $request = $request->withMethod($request->getParsedBody()['_method']);
            }
            /*
            if ($request->getBody()->eof()) {
                $request->getBody()->rewind();
            }*/
        }

        // TODO : faire un throw HttpException 405 si la méthode override n'est pas correcte

        $response = $handler->handle($request);

        return $response;
    }
}
