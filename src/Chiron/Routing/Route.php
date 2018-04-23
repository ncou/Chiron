<?php

declare(strict_types=1);

namespace Chiron\Routing;

//https://docs.zendframework.com/zend-expressive/v2/cookbook/route-specific-pipeline/

// TODO ; regarder exemple ici =>   https://github.com/ncou/router-based-on-AltoRouter/blob/master/src/Route.php

// TODO : regarder exemple ici : https://github.com/zendframework/zend-expressive-router/blob/master/src/Route.php

// TODO : utiliser un systéme de cache avec un serialize/unserialize pour charger les routes en cache : https://github.com/symfony/routing/blob/master/Route.php#L68

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

//use Chiron\Stack\StackAwareTrait;

/**
 * Route class.
 * This class represents single application route.
 *
 * @author <milos@caenazzo.com>
 */
class Route implements RequestHandlerInterface
{
//    use StackAwareTrait;

    private $requirements = [];
    private $defaults = [];
    private $schemes = [];

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

    /**
     * @var array Array of default match types (regex helpers)
     */
    private $matchTypes = [
        'i'  => '[0-9]++',
        'a'  => '[0-9A-Za-z]++',
        'h'  => '[0-9A-Fa-f]++',
        '*'  => '.+?',
        '**' => '.++',
        ''   => '[^/\.]++',
    ];

    public const REGEX_PATTERN = '`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`';

    /**
     * @param $url
     * @param $requestMethod
     * @param $class
     * @param $function
     */
    public function __construct(string $url, RequestHandlerInterface $handler)
    {
        $this->url = $url;
        $this->handler = $handler;

        /*
        //TODO : ajouter ce bout de code dans la méthode allowMethods() ou directement dans la partie setMethods()
                if (in_array('GET', $this->methods) && ! in_array('HEAD', $this->methods)) {
                    $this->methods[] = 'HEAD';
                }
                */
    }

    // TODO ; vérifier l'utilité de cette méthode
    public function getUrl()
    {
        return $this->url;
    }

    // TODO ; vérifier l'utilité de cette méthode
    /*
    public function getHandler()
    {
        return $this->handler;
    }*/

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->handler->handle($request);
    }

    /**
     * Compile the regex for a given route (EXPENSIVE).
     */
    public function compile(string $route): string
    {
        if (preg_match_all(self::REGEX_PATTERN, $route, $matches, PREG_SET_ORDER)) {
            $matchTypes = $this->matchTypes;
            foreach ($matches as $match) {
                list($block, $pre, $type, $param, $optional) = $match;

                if (isset($matchTypes[$type])) {
                    $type = $matchTypes[$type];
                }

                // overide the regex control, to use the value defined in the "assert()" function
                if ($this->hasRequirement($param)) {
                    $type = $this->getRequirement($param);
                }

                // init the param with the default value (if it exist) in the array used to store the values after the match() function
                /*
                if ($this->hasDefault($param)) {
                    $this->params[$param] = $this->getDefault($param);
                }*/

                if ($pre === '.') {
                    $pre = '\.';
                }

                // TODO : eventuellement regarder dans le tableau "default" si il n'y a pas des clés qui ne sont pas rattachées à un paramétre "optionnel", car si on ajoute des valeurs par défault sur des paramétres qui ne sont pas optionnels, on va augmenter le nombre de "params" qui vont être retournés à la méthode de callback. Grossomodo, si on a ajouter une clé dans le tableau default et que cette clé n'est pas un param optionnel (? dans la regex), alors il faut virer cette clé du tableau default !!!!!
                $optional = $optional !== '' ? '?' : null;

                //Older versions of PCRE require the 'P' in (?P<named>)
                $pattern = '(?:'
                        . ($pre !== '' ? $pre : null)
                        . '('
                        . ($param !== '' ? "?P<$param>" : null)
                        . $type
                        . ')'
                        . $optional
                        . ')'
                        . $optional;

                $route = str_replace($block, $pattern, $route);
            }
        }

        return "`^$route$`u";
    }

    /**
     * Reversed routing.
     *
     * Generate the URL for a named route. Replace regexes with supplied substitutions values
     *
     * @param array $substitutions
     *
     * @return string
     */
    public function generate(array $substitutions = []): string
    {
        $url = $this->url;

        if (preg_match_all(self::REGEX_PATTERN, $this->url, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                list($block, $pre, $type, $param, $optional) = $match;

                if ($pre) {
                    $block = substr($block, 1);
                }

                if (isset($substitutions[$param])) {
                    $url = str_replace($block, $substitutions[$param], $url);
                } elseif ($optional) {
                    $url = str_replace($pre . $block, '', $url);
                }
            }
        }

        return $url;
    }

    /**
     * Check if the method used as parameter is supported/allowed in this route.
     *
     * @param $methods string/array HTTP Method
     *
     * @return bool
     */
    // TODO : regarder aussi ici comment c'est fait pour HEAD et GET : https://github.com/klein/klein.php/blob/b65c53c605d500caaea0d269f7970cccbb26bbba/src/Klein/Klein.php#L686
    // TODO : regarder aussi ici comment c'est fait pour HEAD et GET : https://github.com/symfony/routing/blob/a031adc974a737fe9880b652b551435db2629e98/Matcher/UrlMatcher.php#L141
    // TODO : https://github.com/narrowspark/framework/blob/master/src/Viserio/Component/Routing/Route.php#L84
    // TODO : passer en paramétre une string uniquement, et pas un tableau !!!!!!!!!!!!!
    /*
    public function allowMethod($methods){
        $methods = array_map('strtoupper',(array)$methods);
        foreach((array)$methods as $method){
            if($method === 'HEAD'){
                $method = 'GET';
            }
            if(in_array($method, $this->methods)){
                return true;
            }
        }
        return false;
    }*/

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

    /*
    //https://github.com/ncou/super-sharp-router/blob/master/src/Route.php#L61
        protected function generateName()
        {
            $requirements = $this->getRequirements();
            $method = isset($requirements['_method']) ? $requirements['_method'] : '';

            $routeName = $method.$this->getPath();
            $routeName = str_replace(array('/', ':', '|', '-'), '_', $routeName);
            $routeName = preg_replace('/[^a-z0-9A-Z_.]+/', '', $routeName);
            return $routeName;
        }
    */

    /*
        public function method($method)
        {
            $this->setAllowedMethods(explode('|', $method));
            return $this;
        }*/

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
     * Validate the provided HTTP method names.
     *
     * Validates, and then normalizes to upper case.
     *
     * @param string[] An array of HTTP method names.
     *
     * @throws Exception\InvalidArgumentException for any invalid method names.
     *
     * @return string[]
     */
    // TODO : regarder aussi ici : https://github.com/slimphp/Slim-Http/blob/master/src/Request.php#L269
    // https://github.com/zendframework/zend-diactoros/blob/master/src/RequestTrait.php#L283
    //https://github.com/zendframework/zend-expressive-router/blob/master/src/Route.php#L170
    /*
    private function validateHttpMethods(array $methods) : array
    {
        if (empty($methods)) {
            throw new Exception\InvalidArgumentException(
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
            throw new Exception\InvalidArgumentException('One or more HTTP methods were invalid');
        }
        return array_map('strtoupper', $methods);
    }*/

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
     * Get supported HTTP method(s).
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        //return array_unique($this->methods);
        return $this->methods;
    }

    /*
     * Get route callable
     *
     * @return callable
     */
    /*
    public function getCallable()
    {
        return $this->callable;
    }*/
    /*
     * This method enables you to override the Route's callable
     *
     * @param string|\Closure $callable
     */
    /*
    public function setCallable($callable)
    {
        $this->callable = $callable;
    }*/

    /*
     * Set callable resolver
     *
     * @param CallableResolverInterface $resolver
     */
    /*
    public function setCallableResolver(CallableResolverInterface $resolver)
    {
        $this->callableResolver = $resolver;
    }*/
    /*
     * Get callable resolver
     *
     * @return CallableResolverInterface|null
     */
    /*
    public function getCallableResolver()
    {
        return $this->callableResolver;
    }*/

    /*
     * Dispatch route callable against current Request and Response objects
     *
     * This method invokes the route object's callable. If middleware is
     * registered for the route, each callable middleware is invoked in
     * the order specified.
     *
     * @param ServerRequestInterface $request  The current Request object
     * @param ResponseInterface      $response The current Response object
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Exception  if the route callable throws an exception
     */
    /*
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        // Resolve route callable
        $callable = $this->callable;
        if ($this->callableResolver) {
            $callable = $this->callableResolver->resolve($callable);
        }
        // @var InvocationStrategyInterface $handler
        $handler = $this->routeInvocationStrategy;
        $routeResponse = $handler($callable, $request, $response, $this->arguments);
        if (! $routeResponse instanceof ResponseInterface) {
            throw new \RuntimeException('Route handler must return instance of \Psr\Http\Message\ResponseInterface');
        }
        return $routeResponse;
    }*/
}
