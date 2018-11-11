<?php

declare(strict_types=1);

namespace Chiron\Routing;

use Chiron\Routing\Strategy\StrategyAwareInterface;
use Chiron\Routing\Strategy\StrategyAwareTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Route implements RouteConditionHandlerInterface, StrategyAwareInterface, MiddlewareAwareInterface, MiddlewareInterface
{
    use MiddlewareAwareTrait;
    use RouteConditionHandlerTrait;
    use StrategyAwareTrait;

    /** @var array */
    private $requirements = [];

    /** @var array */
    private $defaults = [];

    /** @var array */
    private $schemes = [];

    /** @var string */
    private $name;

    /**
     * The route pattern (The URL pattern (e.g. "article/[:year]/[i:category]")).
     *
     * @var string
     */
    private $url;

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

    /** @var RouteGroup */
    private $group;

    /**
     * Route identifier.
     *
     * @var string
     */
    private $identifier;

    /**
     * @param $url string
     * @param $handler mixed
     */
    // TODO : passer en paramétre aussi les middlewares, un truc comme __construct(...., $middlewares = [])
    public function __construct(string $url, $handler, int $index)
    {
        $this->url = $url;
        $this->handler = $handler;
        $this->identifier = 'route_' . $index;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $requestHandler
    ): ResponseInterface {
        return $this->getStrategy()->invokeRouteCallable($this, $request);
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
    public function setParentGroup(RouteGroup $group)//: Route
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

    // TODO ; renommer en getPath()
    public function getUrl(): string
    {
        return $this->url;
    }

    // return : mixed
    public function getHandler()
    {
        return $this->handler;
    }

    public function value($variable, $default)
    {
        $this->setDefault($variable, $default);

        return $this;
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

    //https://github.com/silexphp/Silex/blob/master/src/Silex/Route.php#L138

    /**
     * Sets the requirement of HTTP (no HTTPS) on this Route.
     *
     * @return Route $this The current Route instance
     */
    public function requireHttp()
    {
        $this->setSchemes('http');

        return $this;
    }

    /**
     * Sets the requirement of HTTPS on this Route.
     *
     * @return Route $this The current Route instance
     */
    public function requireHttps()
    {
        $this->setSchemes('https');

        return $this;
    }

    //https://github.com/symfony/routing/blob/master/Route.php

    /**
     * Returns the lowercased schemes this route is restricted to.
     * So an empty array means that any scheme is allowed.
     *
     * @return string[] The schemes
     */
    public function getSchemes()
    {
        return $this->schemes;
    }

    /**
     * Sets the schemes (e.g. 'https') this route is restricted to.
     * So an empty array means that any scheme is allowed.
     *
     * This method implements a fluent interface.
     *
     * @param string|string[] $schemes The scheme or an array of schemes
     *
     * @return $this
     */
    public function setSchemes($schemes)
    {
        $this->schemes = array_map('strtolower', (array) $schemes);
        //$this->compiled = null;
        return $this;
    }

    /**
     * Checks if a scheme requirement has been set.
     *
     * @param string $scheme
     *
     * @return bool true if the scheme requirement exists, otherwise false
     */
    public function hasScheme($scheme)
    {
        return in_array(strtolower($scheme), $this->schemes, true);
    }

    public function name(string $name)
    {
        //TODO : mettre en place une vérif pour éviter qu'on ait pas des doublons de noms pour les routes. Eventuellement faire ce controle côté router !!!!
        /*
                if (!is_string($name)) {
                    throw new InvalidArgumentException('Route name must be a string');
                }
        */
        /*
                if (isset($this->name)) {
                    throw new \Exception("Can not redeclare route '{$this->name}'");
                }
        */
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
        //return $this->name?: $this->generateName();
    }

    public function method(string $method, string ...$methods)
    {
        array_unshift($methods, $method);
        $this->setAllowedMethods($methods);

        return $this;
    }

    /**
     * Set supported HTTP method(s).
     *
     * @param array
     *
     * @return self
     */
    public function setAllowedMethods(array $methods)
    {
        // Allow null, otherwise expect an array or a string
        /*
//https://github.com/klein/klein.php/blob/master/src/Klein/Route.php#L172
        if (null !== $method && !is_array($method) && !is_string($method)) {
            throw new InvalidArgumentException('Expected an array or string. Got a '. gettype($method));
        }
*/
        // TODO : ajouter une vérification concernant la méthode ajoutée en la validant avec un regex : https://github.com/zendframework/zend-expressive-router/blob/master/src/Route.php#L170
        $this->methods = array_map('strtoupper', $methods);

        return $this;
    }

    /**
     * Get supported HTTP method(s).
     *
     * @return array
     */
    public function getAllowedMethods(): array
    {
        //return array_unique($this->methods);
        return $this->methods;
    }
}
