<?php

declare(strict_types=1);

namespace Chiron\Routing;

use Chiron\Routing\Traits\StrategyAwareInterface;
use Chiron\Routing\Traits\StrategyAwareTrait;
use Chiron\Routing\Traits\MiddlewareAwareTrait;
use Chiron\Routing\Traits\MiddlewareAwareInterface;
use Chiron\Routing\Traits\RouteConditionHandlerTrait;
use Chiron\Routing\Traits\RouteConditionHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use InvalidArgumentException;

class Route implements RouteConditionHandlerInterface, StrategyAwareInterface, MiddlewareAwareInterface, MiddlewareInterface
{
    use MiddlewareAwareTrait;
    use RouteConditionHandlerTrait;
    use StrategyAwareTrait;

    /** @var array */
    private $requirements = [];

    /** @var array */
    private $defaults = [];


    /** @var string|null */
    private $name;

    /**
     * The route path pattern (The URL pattern (e.g. "article/[:year]/[i:category]")).
     *
     * @var string
     */
    private $path;

    /**
     * Controller/method assigned to be executed when route is matched.
     *
     * @var mixed
     */
    private $handler;

    /**
     * List of supported HTTP methods for this route (GET, POST etc.).
     *
     * @var array
     */
    private $methods = [];

    /** @var null|RouteGroup */
    private $group;

    /**
     * Route identifier.
     *
     * @var string
     */
    private $identifier;

    /**
     * @var array
     */
    private $vars = [];

    /**
     * @param string $url
     * @param mixed $handler
     */
    public function __construct(string $path, $handler, int $index)
    {
        $this->path = $path;
        $this->handler = $handler;
        $this->identifier = 'route_' . $index;
    }

    /**
     * Get the parent group.
     *
     * @return null|RouteGroup
     */
    public function getParentGroup(): ?RouteGroup
    {
        return $this->group;
    }

    /**
     * Set the parent group.
     *
     * @param RouteGroup $group
     *
     * @return Route
     */
    public function setParentGroup(RouteGroup $group): self
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get route identifier.
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    // return : mixed
    public function getHandler()
    {
        return $this->handler;
    }




    /**
     * Returns the defaults.
     *
     * @return array The defaults
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * Sets the defaults.
     *
     * This method implements a fluent interface.
     *
     * @param array $defaults The defaults
     *
     * @return $this
     */
    public function setDefaults(array $defaults)
    {
        $this->defaults = [];

        return $this->addDefaults($defaults);
    }

    /**
     * Adds defaults.
     *
     * This method implements a fluent interface.
     *
     * @param array $defaults The defaults
     *
     * @return $this
     */
    public function addDefaults(array $defaults)
    {
        foreach ($defaults as $name => $default) {
            $this->defaults[$name] = $default;
        }
        //$this->compiled = null;
        return $this;
    }

    /**
     * Gets a default value.
     *
     * @param string $name A variable name
     *
     * @return mixed The default value or null when not given
     */
    public function getDefault($name)
    {
        return isset($this->defaults[$name]) ? $this->defaults[$name] : null;
    }

    /**
     * Checks if a default value is set for the given variable.
     *
     * @param string $name A variable name
     *
     * @return bool true if the default value is set, false otherwise
     */
    public function hasDefault($name)
    {
        return array_key_exists($name, $this->defaults);
    }

    /**
     * Sets a default value.
     *
     * @param string $name    A variable name
     * @param mixed  $default The default value
     *
     * @return $this
     */
    public function setDefault($name, $default)
    {
        $this->defaults[$name] = $default;
        //$this->compiled = null;
        return $this;
    }

    public function value($variable, $default)
    {
        $this->setDefault($variable, $default);

        return $this;
    }






    // TODO : avoir la possibilité de passer un tableau ? si on détecte que c'est un is_array dans le getargs() on appel la méthode addReqirements() pour un tableau, sinon on appel setRequirement()
    public function assert($key, $regex)
    {
        $this->setRequirement($key, $regex);

        return $this;
    }

    /**
     * Returns the requirements.
     *
     * @return array The requirements
     */
    public function getRequirements()
    {
        return $this->requirements;
    }

    /**
     * Sets the requirements.
     *
     * This method implements a fluent interface.
     *
     * @param array $requirements The requirements
     *
     * @return $this
     */
    public function setRequirements(array $requirements)
    {
        $this->requirements = [];

        return $this->addRequirements($requirements);
    }

    /**
     * Adds requirements.
     *
     * This method implements a fluent interface.
     *
     * @param array $requirements The requirements
     *
     * @return $this
     */
    public function addRequirements(array $requirements)
    {
        foreach ($requirements as $key => $regex) {
            $this->requirements[$key] = $this->sanitizeRequirement($key, $regex);
        }
        //$this->compiled = null;
        return $this;
    }

    /**
     * Returns the requirement for the given key.
     *
     * @param string $key The key
     *
     * @return string|null The regex or null when not given
     */
    public function getRequirement($key)
    {
        return isset($this->requirements[$key]) ? $this->requirements[$key] : null;
    }

    /**
     * Checks if a requirement is set for the given key.
     *
     * @param string $key A variable name
     *
     * @return bool true if a requirement is specified, false otherwise
     */
    public function hasRequirement($key)
    {
        return array_key_exists($key, $this->requirements);
    }

    /**
     * Sets a requirement for the given key.
     *
     * @param string $key   The key
     * @param string $regex The regex
     *
     * @return $this
     */
    public function setRequirement($key, $regex)
    {
        $this->requirements[$key] = $this->sanitizeRequirement($key, $regex);
        //$this->compiled = null;
        return $this;
    }

    // remove the char "^" at the start of the regex, and the final "$" char at the end of the regex
    private function sanitizeRequirement($key, $regex)
    {
        if (! is_string($regex)) {
            throw new \InvalidArgumentException(sprintf('Routing requirement for "%s" must be a string.', $key));
        }
        if ('' !== $regex && '^' === $regex[0]) {
            $regex = (string) substr($regex, 1); // returns false for a single character
        }
        if ('$' === substr($regex, -1)) {
            $regex = substr($regex, 0, -1);
        }
        if ('' === $regex) {
            throw new \InvalidArgumentException(sprintf('Routing requirement for "%s" cannot be empty.', $key));
        }

        return $regex;
    }











    /**
     * Get the route name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set the route name
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Alia function for "setName()".
     *
     * @param string $name
     *
     * @return $this
     */
    public function name(string $name): self
    {
        return $this->setName($name);
    }








    /**
     * Get supported HTTP method(s).
     *
     * @return array
     */
    public function getAllowedMethods(): array
    {
        return array_unique($this->methods);
    }

    /**
     * Set supported HTTP method(s).
     *
     * @param array
     *
     * @return self
     */
    public function setAllowedMethods(array $methods) : Route
    {
        $this->methods = $this->validateHttpMethods($methods);

        return $this;
    }

    /**
     * Alia function for "setAllowedMethods()".
     */
    public function method(string $method, string ...$methods): Route
    {
        array_unshift($methods, $method);
        return $this->setAllowedMethods($methods);
    }

    /**
     * Validate the provided HTTP method names.
     *
     * Validates, and then normalizes to upper case.
     *
     * @param string[] An array of HTTP method names.
     * @return string[]
     * @throws Exception\InvalidArgumentException for any invalid method names.
     */
    private function validateHttpMethods(array $methods) : array
    {
        if (empty($methods)) {
            throw new InvalidArgumentException(
                'HTTP methods argument was empty; must contain at least one method'
            );
        }
        if (false === array_reduce($methods, function ($valid, $method) {
            if (false === $valid) {
                return false;
            }
            if (! is_string($method)) {
                return false;
            }
            if (! preg_match('/^[!#$%&\'*+.^_`\|~0-9a-z-]+$/i', $method)) {
                return false;
            }
            return $valid;
        }, true)) {
            throw new InvalidArgumentException('One or more HTTP methods were invalid');
        }
        return array_map('strtoupper', $methods);
    }

    /**
     * Indicate whether the specified method is allowed by the route.
     *
     * @param string $method HTTP method to test.
     */
    /*
    public function allowsMethod(string $method) : bool
    {
        $method = strtoupper($method);
        if ($this->methods === self::HTTP_METHOD_ANY
            || in_array($method, $this->methods, true)
        ) {
            return true;
        }
        return false;
    }*/



    /**
     * Return variables to be passed to route callable
     *
     * @return array
     */
    public function getVars() : array
    {
        return $this->vars;
    }
    /**
     * Set variables to be passed to route callable
     *
     * @param array $vars
     *
     * @return $this
     */
    public function setVars(array $vars) : self
    {
        $this->vars = $vars;
        return $this;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        return $this->getStrategy()->invokeRouteCallable($this, $request);
    }
}
