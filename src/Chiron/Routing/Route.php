<?php

declare(strict_types=1);

namespace Chiron\Routing;

use Chiron\Routing\Traits\MiddlewareAwareInterface;
use Chiron\Routing\Traits\MiddlewareAwareTrait;
use Chiron\Routing\Traits\RouteConditionHandlerInterface;
use Chiron\Routing\Traits\RouteConditionHandlerTrait;
use Chiron\Routing\Traits\StrategyAwareInterface;
use Chiron\Routing\Traits\StrategyAwareTrait;
use InvalidArgumentException;
use LogicException;

class Route implements RouteConditionHandlerInterface, StrategyAwareInterface, MiddlewareAwareInterface
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
    private $methods = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'TRACE'];

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
    private $parameters;

    /**
     * @var bool
     */
    private $isBinded = false;

    /**
     * @param string $url
     * @param mixed  $handler should be a string or a callable
     * @param int    $index
     */
    public function __construct(string $path, $handler, int $index)
    {
        // A path must start with a slash and must not have multiple slashes at the beginning because it would be confused with a network path, e.g. '//domain.com/path'.
        $this->path = '/'.ltrim(trim($path), '/'); // sprintf('/%s', ltrim($path, '/'));
        // TODO : ajouter une vérification pour que le $handler soit un callable ou une string
        $this->handler = $handler;
        $this->identifier = 'route_' . $index;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    // return : mixed => should be a string or a callable
    public function getHandler()
    {
        return $this->handler;
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
     * Returns the defaults.
     *
     * @return array The defaults
     */
    public function getDefaults(): array
    {
        return $this->defaults;
    }

    /**
     * Sets the defaults.
     *
     * @param array $defaults The defaults
     *
     * @return $this
     */
    public function setDefaults(array $defaults): self
    {
        $this->defaults = [];

        return $this->addDefaults($defaults);
    }

    /**
     * Adds defaults.
     *
     * @param array $defaults The defaults
     *
     * @return $this
     */
    public function addDefaults(array $defaults): self
    {
        // TODO : faire un assert que $name est bien une string sinon lever une exception !!!!
        foreach ($defaults as $name => $default) {
            $this->defaults[$name] = $default;
        }

        return $this;
    }

    /**
     * Gets a default value.
     *
     * @param string $name A variable name
     *
     * @return mixed The default value or null when not given
     */
    public function getDefault(string $name)
    {
        return $this->defaults[$name] ?? null;
    }

    /**
     * Checks if a default value is set for the given variable.
     *
     * @param string $name A variable name
     *
     * @return bool true if the default value is set, false otherwise
     */
    public function hasDefault(string $name): bool
    {
        return array_key_exists($name, $this->defaults);
    }

    /**
     * Alias for setDefault.
     *
     * @param string $name    A variable name
     * @param mixed  $default The default value
     *
     * @return $this
     */
    public function value(string $variable, $default): self
    {
        return $this->setDefault($variable, $default);
    }

    /**
     * Sets a default value.
     *
     * @param string $name    A variable name
     * @param mixed  $default The default value
     *
     * @return $this
     */
    public function setDefault(string $name, $default): self
    {
        $this->defaults[$name] = $default;

        return $this;
    }

    /**
     * Returns the requirements.
     *
     * @return array The requirements
     */
    public function getRequirements(): array
    {
        return $this->requirements;
    }

    /**
     * Sets the requirements.
     *
     * @param array $requirements The requirements
     *
     * @return $this
     */
    public function setRequirements(array $requirements): self
    {
        $this->requirements = [];

        return $this->addRequirements($requirements);
    }

    /**
     * Adds requirements.
     *
     * @param array $requirements The requirements
     *
     * @return $this
     */
    public function addRequirements(array $requirements): self
    {
        // TODO : lever une exception si la key et le $regex ne sont pas des strings !!!!!
        /*
        if (! is_string($regex)) {
            throw new InvalidArgumentException(sprintf('Routing requirement for "%s" must be a string.', $key));
        }*/

        foreach ($requirements as $key => $regex) {
            $this->requirements[$key] = $this->sanitizeRequirement($key, $regex);
        }

        return $this;
    }

    /**
     * Returns the requirement for the given key.
     *
     * @param string $key The key
     *
     * @return string|null The regex or null when not given
     */
    public function getRequirement(string $key): ?string
    {
        return $this->requirements[$key] ?? null;
    }

    /**
     * Checks if a requirement is set for the given key.
     *
     * @param string $key A variable name
     *
     * @return bool true if a requirement is specified, false otherwise
     */
    public function hasRequirement(string $key): bool
    {
        return array_key_exists($key, $this->requirements);
    }

    // TODO : avoir la possibilité de passer un tableau ? si on détecte que c'est un is_array dans le getargs() on appel la méthode addReqirements() pour un tableau, sinon on appel setRequirement()
    public function assert(string $key, string $regex): self
    {
        return $this->setRequirement($key, $regex);
    }

    /**
     * Sets a requirement for the given key.
     *
     * @param string $key   The key
     * @param string $regex The regex
     *
     * @return $this
     */
    public function setRequirement(string $key, string $regex): self
    {
        $this->requirements[$key] = $this->sanitizeRequirement($key, $regex);

        return $this;
    }

    // remove the char "^" at the start of the regex, and the final "$" char at the end of the regex
    private function sanitizeRequirement(string $key, string $regex): string
    {
        if ('' !== $regex && '^' === $regex[0]) {
            $regex = substr($regex, 1); // returns false for a single character
        }
        if ('$' === substr($regex, -1)) {
            $regex = substr($regex, 0, -1);
        }
        if ('' === $regex) {
            throw new InvalidArgumentException(sprintf('Routing requirement for "%s" cannot be empty.', $key));
        }

        return $regex;
    }

    /**
     * Get the route name.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
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
     * Set the route name.
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
     * Get supported HTTP method(s).
     *
     * @return array
     */
    public function getAllowedMethods(): array
    {
        return array_unique($this->methods);
    }

    /**
     * Alia function for "setAllowedMethods()".
     * @param string or list of string
     */
    public function method(string $method, string ...$methods): self
    {
        array_unshift($methods, $method);

        return $this->setAllowedMethods($methods);
    }

    /**
     * Set supported HTTP method(s).
     *
     * @param array
     *
     * @return self
     */
    public function setAllowedMethods(array $methods): self
    {
        $this->methods = $this->validateHttpMethods($methods);

        return $this;
    }

    /**
     * Validate the provided HTTP method names.
     *
     * Validates, and then normalizes to upper case.
     *
     * @param string[] An array of HTTP method names.
     *
     * @throws Exception InvalidArgumentException for any invalid method names.
     *
     * @return string[]
     */
    private function validateHttpMethods(array $methods): array
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
}
