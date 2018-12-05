<?php

/**
 * PhalconSlayer\Framework.
 *
 * @copyright 2015-2016 Daison Carino <daison12006013@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://docs.phalconslayer.com
 */
if (! function_exists('application')) {

    /**
     * This returns the service provider 'application'.
     *
     * @return Phalcon\Mvc\Application
     */
    function application()
    {
        return di()->get('application');
    }
}

if (! function_exists('auth')) {

    /**
     * This returns the service provider 'auth'.
     *
     * @return Clarity\Support\Auth\Auth
     */
    function auth()
    {
        return di()->get('auth');
    }
}

if (! function_exists('cache')) {

    /**
     * This returns the service provider 'cache'.
     *
     * @param string|mixed $option If string passed, it will automatically
     * interpret as ->get(...), if array the index 0 will be the key and value
     * will be index 1
     * @return Phalcon\Cache\Backend
     */
    function cache($option = null)
    {
        $cache = di()->get('cache');

        if (is_string($option)) {
            return $cache->get($option);
        }

        if (is_array($option)) {
            if (! isset($option[0]) || ! isset($option[1])) {
                throw new InvalidArgumentException('Array must have index[0] for cache alias and index[1] for the value');
            }

            $cache->save($option[0], $option[1]);

            return true;
        }

        return $cache;
    }
}

if (! function_exists('config')) {

    /**
     * This returns the service provider 'config'.
     *
     * @param string|mixed $option If string passed, it will automatically
     *      interpret as ->get(...), if array it will merge/replace based on
     *      [$override = true]
     * @param bool $override If true, it will automatically replace/override a config value.
     * @return mixed
     */
    // TODO : attention, il faut modifier la documentation + le typehint pour le retour car on peut retourner un booléen.
    function config($option = null, $override = true)
    {
        $config = di()->get('config');

        # here, if the $option is null
        # we should directly pass the di 'config'
        if ($option === null) {
            return $config;
        }

        # here, if the option is array
        # it should interpreted as updating the di 'config'
        # current structure
        if (is_array($option)) {
            $config->merge($option, $override);

            return $config;
        }

        return $config->get($option);
    }
}

if (! function_exists('db')) {

    /**
     * This returns the service provider 'db'.
     *
     * @return Clarity\Providers\DB
     */
    function db()
    {
        return di()->get('db');
    }
}

if (! function_exists('dispatcher')) {

    /**
     * This returns the service provider 'dispatcher'.
     *
     * @return Phalcon\Mvc\Dispatcher
     */
    function dispatcher()
    {
        return di()->get('dispatcher');
    }
}

if (! function_exists('filter')) {

    /**
     * This returns the service provider 'filter'.
     *
     * @return Phalcon\Filter
     */
    function filter()
    {
        return di()->get('filter');
    }
}

if (! function_exists('flash')) {

    /**
     * This returns the service provider 'flash'.
     *
     * @return Clarity\Providers\Flash
     */
    function flash()
    {
        return di()->get('flash');
    }
}

if (! function_exists('flysystem')) {

    /**
     * This returns the service provider 'flysystem',
     * If you passed a value in argument 1, it will call ->get($path) instead.
     *
     * @param string $path The path to be referenced
     * @return League\Flysystem\Filesystem
     */
    function flysystem($path = null)
    {
        $flysystem = di()->get('flysystem');

        # here, if there is assigned path
        # it is the path to access the requested file
        if ($path !== null) {
            return $flysystem->get($path);
        }

        return $flysystem;
    }
}

if (! function_exists('flysystem_manager')) {

    /**
     * This returns the service provider 'flsystem_manager',
     * If you passed a value in argument 1, it will create a local adapter
     * based on the provided path.
     *
     * @param string $path The local path to be referenced
     * @return League\Flysystem\MountManager
     */
    function flysystem_manager($path = null)
    {
        # here, if there is assigned path
        # we should directly create an instance
        # based on the $path provided
        # whilist the adapter is 'local'
        if ($path !== null) {
            return new League\Flysystem\Filesystem(
                new League\Flysystem\Adapter\Local($path, 0)
            );
        }

        return di()->get('flysystem_manager');
    }
}

if (! function_exists('lang')) {

    /**
     * This returns the service provider 'lang'.
     *
     * @return Clarity\Lang\Lang
     */
    function lang()
    {
        return di()->get('lang');
    }
}

if (! function_exists('logger')) {

    /**
     * This returns the service provider 'log'.
     *
     * @return Monolog\Logger
     */
    function logger()
    {
        return di()->get('log');
    }
}

if (! function_exists('queue')) {

    /**
     * This returns the service provider 'queue'.
     *
     * @return Clarity\Support\Queue\Queue
     */
    function queue($class = null, $data = [], $options = [])
    {
        $queue = di()->get('queue');

        if (empty($class)) {
            return $queue;
        }

        return $queue->put([
            'class' => $class,
            'data'  => $data,
        ], $options);
    }
}

if (! function_exists('redirect')) {

    /**
     * This returns the service provider 'redirect'.
     *
     * @param string $to To be interpreted as uri, where 'to' redirect
     * @return Clarity\Support\Redirect\Redirect|mixed
     */
    function redirect($to = null)
    {
        $redirect = di()->get('redirect');
        if ($to === null) {
            return $redirect;
        }

        return $redirect->to($to);
    }
}

if (! function_exists('request')) {

    /**
     * This returns the service provider 'request'.
     *
     * @return Clarity\Support\Phalcon\Http\Request
     */
    function request()
    {
        return di()->get('request');
    }
}

if (! function_exists('response')) {

    /**
     * This returns the service provider 'response'.
     *
     * @return Phalcon\Http\Response
     */
    function response()
    {
        return di()->get('response');
    }
}

if (! function_exists('route')) {

    /**
     * This returns the service provider 'route', if you passed a value on first argument
     * it will call the url() helper instead and will call ->route($name, $params, $raw).
     *
     * @param string $name The route name
     * @param mixed $params A uri parameters for this route
     * @param mixed $raw A raw parameters for your route
     * @return Clarity\Support\Phalcon\Mvc\Router|string
     */
    function route($name = null, $params = [], $raw = [])
    {
        if ($name === null) {
            return di()->get('router');
        }

        return url()->route($name, $params, $raw);
    }
}

if (! function_exists('security')) {

    /**
     * This returns the service provider 'security'.
     *
     * @return Phalcon\Security
     */
    function security()
    {
        return di()->get('security');
    }
}

if (! function_exists('session')) {

    /**
     * This returns the service provider 'session'.
     *
     * @return Phalcon\Session\Adapter
     */
    function session()
    {
        return di()->get('session');
    }
}

if (! function_exists('tag')) {

    /**
     * This returns the service provider 'tag'.
     *
     * @return Phalcon\Tag
     */
    function tag()
    {
        return di()->get('tag');
    }
}

if (! function_exists('url')) {

    /**
     * This returns the service provider 'url', if $href filled with uri,
     * it should automatically call the ->get($href, $params = []);.
     *
     * @param string $href The uri/url to use
     * @param mixed $params The parameters to append
     * @return Clarity\Support\Phalcon\Mvc\URL|string
     */
    function url($href = null, $params = [])
    {
        $url = di()->get('url');

        if ($href === null) {
            return $url;
        }

        return $url->get($href, $params);
    }
}

if (! function_exists('view')) {

    /**
     * This returns the service provider 'view',
     * If a value passed in argument 1, it will call the view instance along
     * ->make(<argument 1>, $params = [] | <argument 2>).
     *
     * @param string $path
     * @param mixed $params
     * @return Clarity\Support\Phalcon\Mvc\View|string
     */
    function view($path = null, $params = [])
    {
        $view = di()->get('view');

        if ($path === null) {
            return $view;
        }

        return $view->make($path, $params);
    }
}
