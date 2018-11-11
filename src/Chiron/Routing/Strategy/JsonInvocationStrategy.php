<?php

declare(strict_types=1);

// https://github.com/symfony/http-kernel/blob/3.3/Tests/Controller/ControllerResolverTest.php

namespace Chiron\Routing\Strategy;

use Chiron\Http\Psr\Response;
use Chiron\Routing\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Route callback strategy with route parameters as individual arguments.
 */
class JsonInvocationStrategy extends AbstractStrategy
{
    /** CallableResolverInterface */
    private $resolver;

    public function __construct(CallableResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    public function invokeRouteCallable(Route $route, ServerRequestInterface $request): ResponseInterface
    {
        $callable = $this->resolver->resolve($route->getHandler());
        $parameters = $this->getParametersFromCallable($callable);
        $arguments = $this->bindAttributesWithParameters($parameters, $request);

        $response = call_user_func_array($callable, $arguments);

        if ($this->isJsonEncodable($response)) {
            $json = json_encode($response); // json_encode($data, $encodingOptions)); // TODO : lui passer des options pour le json encode

            // TODO : améliorer la gestion des exceptions :
            //https://github.com/knpuniversity/twig/blob/master/start/vendor/symfony/http-foundation/Symfony/Component/HttpFoundation/JsonResponse.php#L86
            //https://github.com/zendframework/zend-diactoros/blob/master/src/Response/JsonResponse.php#L144
            //https://github.com/illuminate/http/blob/master/JsonResponse.php#L64
            //https://api.drupal.org/api/drupal/vendor%21symfony%21http-foundation%21JsonResponse.php/8.4.x
            //http://php.net/manual/fr/function.json-encode.php#117615
            //https://github.com/symfony/http-foundation/blob/master/JsonResponse.php#L143

            //https://github.com/sergant210/modHelpers/blob/master/core/components/modhelpers/classes/JsonResponse.php#L57
            //https://github.com/sergant210/modHelpers/blob/master/core/components/modhelpers/classes/ResponseTrait.php#L19
            //https://github.com/sergant210/modHelpers/blob/master/core/components/modhelpers/classes/Response.php#L50

            // Encode <, >, ', &, and " for RFC4627-compliant JSON, which may also be embedded into HTML.
            //$this->data = json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

            // Ensure that the json encoding passed successfully
            if ($json === false) {
                throw new \RuntimeException(json_last_error_msg(), json_last_error());
            }

            //$response = $this->responseFactory->createResponse(200);
            $response = new Response(200);
            $response = $response->withHeader('Content-Type', 'application/json');
            $response->getBody()->write($json);
        }

        return $response;
    }

    /**
     * Check if the response can be converted to JSON.
     *
     * Arrays can always be converted, objects can be converted if they're not a response already
     *
     * @param mixed $response
     *
     * @return bool
     */
    // TODO : regarder ici : https://github.com/sergant210/modHelpers/blob/master/core/components/modhelpers/classes/Response.php#L50
    private function isJsonEncodable($response): bool
    {
        if ($response instanceof ResponseInterface) {
            return false;
        }

        return is_array($response) || is_object($response);
    }
}
