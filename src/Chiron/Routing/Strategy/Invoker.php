<?php

declare(strict_types=1);

// https://github.com/symfony/http-kernel/blob/3.3/Tests/Controller/ControllerResolverTest.php

//https://github.com/Wandu/Router/blob/master/Loader/PsrLoader.php

// TODO : regarder ces deux classes mais ce n'est pas les mêmes attention (extends Object par exemple)
//https://github.com/Raphhh/trex/blob/master/src/TRex/Reflection/CallableReflection.php
//https://github.com/Raphhh/trex-reflection/blob/master/src/CallableReflection.php

// TODO : améliorer la gestion du request => https://github.com/PHP-DI/Silex-Bridge/blob/master/src/Controller/ControllerResolver.php#L72

namespace Chiron\Routing\Strategy;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Resolve the parameters using reflexion and execute the callback.
 */
class Invoker
{
    /**
     * Wrapper around the call_user_func_array function to execute the callable.
     *
     * @param ServerRequestInterface $request
     * @param callable               $callback
     * @param array                  $matched
     *
     * @return mixed
     */
    public function call(ServerRequestInterface $request, callable $callback, array $matched)
    {
        $parameters = $this->bindParameters($request, $callback, $matched);

        return call_user_func_array($callback, $parameters);
    }

    /**
     * Bind the matched parameters from the request with the callable parameters.
     *
     * @param ServerRequestInterface $request
     * @param callable               $controller the callable to be executed
     * @param array                  $matched    the parameters extracted from the uri
     *
     * @return array The
     */
    protected function bindParameters(ServerRequestInterface $request, callable $controller, array $matched): array
    {
        if (is_array($controller)) {
            $reflector = new \ReflectionMethod($controller[0], $controller[1]);
            $controllerName = sprintf('%s::%s()', get_class($controller[0]), $controller[1]);
        } elseif (is_object($controller) && ! $controller instanceof \Closure) {
            $reflector = (new \ReflectionObject($controller))->getMethod('__invoke');
            $controllerName = get_class($controller);
        } else {
            $controllerName = ($controller instanceof \Closure) ? get_class($controller) : $controller;
            $reflector = new \ReflectionFunction($controller);
        }

        $parameters = $reflector->getParameters();

        $bindParams = [];
        foreach ($parameters as $param) {
            // @notice \ReflectionType::getName() is not supported in PHP 7.0, that is why we use __toString()
            $paramType = $param->hasType() ? $param->getType()->__toString() : '';

            if (array_key_exists($param->getName(), $matched)) {
                $bindParams[] = $this->transformToScalar($matched[$param->getName()], $paramType);
            } elseif ($param->getClass() && $param->getClass()->isInstance($request)) {
                //} elseif (ServerRequestInterface::class == $param->getType() || is_subclass_of($param->getType(), ServerRequestInterface::class)) {
                //} elseif (ServerRequestInterface::class == $paramType || is_subclass_of($paramType, ServerRequestInterface::class)) {
                $bindParams[] = $request;
            } elseif ($param->isDefaultValueAvailable()) {
                $bindParams[] = $param->getDefaultValue();
            //} elseif ($param->hasType() && $param->allowsNull()) {
            //    $result[] = null;
            } elseif (empty($paramType) && count($parameters) === 1) {
                // handle special case when there is only 1 parameter and no typehintting.
                // We suppose the user want the request => probably a closure without the typehint "ServerRequestInterface" :(
                $bindParams[] = $request;
            } else {
                // can't find the value, or the default value for the parameter => throw an error
                throw new InvalidArgumentException(sprintf(
                    'Controller "%s" requires that you provide a value for the "$%s" argument (because there is no default value or because there is a non optional argument after this one).',
                    $controllerName,
                    $param->getName()
                ));
            }
        }

        return $bindParams;
    }

    /**
     * Transform parameter to scalar. We don't transform the string type.
     *
     * @param string $parameter the value of param
     * @param string $type      the tpe of param
     *
     * @return int|string|bool|float
     */
    private function transformToScalar(string $parameter, string $type)
    {
        switch ($type) {
            case 'int':
                $parameter = (int) $parameter;

                break;
            case 'bool':
                //TODO : utiliser plutot ce bout de code (il faudra surement faire un lowercase en plus !!!) :     \in_array(\trim($value), ['1', 'true'], true);
                $parameter = (bool) $parameter;

                break;
            case 'float':
                $parameter = (float) $parameter;

                break;
        }

        return $parameter;
    }
}
