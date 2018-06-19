<?php

declare(strict_types=1);

// https://github.com/symfony/http-kernel/blob/3.3/Tests/Controller/ControllerResolverTest.php

namespace Chiron\Routing\Strategy;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

//use Slim\Interfaces\InvocationStrategyInterface;
/**
 * Route callback strategy with route parameters as individual arguments.
 */
class RouteInvocationStrategy //implements InvocationStrategyInterface
{
    private $controllerName;

    /**
     * Invoke a route callable with request, response and all route parameters
     * as individual arguments.
     *
     * @param array|callable         $callable
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param array                  $routeArguments
     *
     * @return mixed
     */
    public function __invoke(
        callable $callable,
        ServerRequestInterface $request
    ) {
        $parameters = $this->getParametersFromCallable($callable);
        $arguments = $this->bindAttributesWithParameters($parameters, $request);

        return call_user_func_array($callable, $arguments);
    }

    // Retrieve the parameter for the callable by Reflexion + store the controller name to use it later if we need to throw an exception and display the controller name
    private function getParametersFromCallable(callable $controller): array
    {
        if (is_array($controller)) {
            $reflector = new \ReflectionMethod($controller[0], $controller[1]);
            $this->controllerName = sprintf('%s::%s()', get_class($controller[0]), $controller[1]);
        } elseif (is_object($controller) && ! $controller instanceof \Closure) {
            $reflector = (new \ReflectionObject($controller))->getMethod('__invoke');
            $this->controllerName = get_class($controller);
        } else {
            $this->controllerName = ($controller instanceof \Closure) ? get_class($controller) : $controller;
            $reflector = new \ReflectionFunction($controller);
        }

        return $reflector->getParameters();
    }

    //TODO : regarder ici : https://github.com/swoft-cloud/swoft-framework/blob/v0.2.6/src/Router/Http/HandlerAdapter.php#162
    private function bindAttributesWithParameters(array $parameters, ServerRequestInterface $request): array
    {
        $attributes = $request->getAttributes();
        $arguments = [];

        foreach ($parameters as $param) {
            // @notice \ReflectionType::getName() is not supported in PHP 7.0, that is why we use __toString()
            $paramType = $param->hasType() ? $param->getType()->__toString() : '';

            if (array_key_exists($param->getName(), $attributes)) {
                $arguments[] = $this->transformToScalar($attributes[$param->getName()], $paramType);
            } elseif ($param->getClass() && $param->getClass()->isInstance($request)) {
                //} elseif (ServerRequestInterface::class == $param->getType() || is_subclass_of($param->getType(), ServerRequestInterface::class)) {
                //} elseif (ServerRequestInterface::class == $paramType || is_subclass_of($paramType, ServerRequestInterface::class)) {
                $arguments[] = $request;
            } elseif ($param->isDefaultValueAvailable()) {
                $arguments[] = $param->getDefaultValue();
            //} elseif ($param->hasType() && $param->allowsNull()) {
            //    $arguments[] = null;
            } elseif (empty($paramType) && count($parameters) === 1) {
                // handle special case when there is only 1 parameter and no typehintting.
                // We suppose the user want the request => probably a closure without the typehint "ServerRequestInterface" :(
                $arguments[] = $request;
            } else {
                // can't find the value, or the default value for the parameter => throw an error
                throw new \InvalidArgumentException(sprintf(
                    'Controller "%s" requires that you provide a value for the "$%s" argument (because there is no default value or because there is a non optional argument after this one).',
                    $this->controllerName,
                    $param->getName()
                ));
            }
        }

        return $arguments;
    }

    /**
     * Transform parameter to scalar.
     *
     * @param mixed  $parameter the value of param
     * @param string $type      the tpe of param
     *
     * @return int|string|bool|float
     */
    // TODO : regarder ici comment c'est fait !!!! : https://github.com/juliangut/slim-routing/blob/master/src/Transformer/AbstractTransformer.php#L49
    private function transformToScalar(string $parameter, string $type)
    {
        switch ($type) {
            case 'int':
                $parameter = (int) $parameter;
                break;
            case 'string':
                $parameter = (string) $parameter; // the "cast" is useless because the parameter is always a string
                break;
            case 'bool':
                $parameter = (bool) $parameter; //TODO : utiliser plutot ce bout de code (il faudra surement faire un lowercase en plus !!!) :     \in_array(\trim($value), ['1', 'true'], true);
                break;
            case 'float':
                $parameter = (float) $parameter;

                break;
        }

        return $parameter;
    }
}
