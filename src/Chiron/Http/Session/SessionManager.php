<?php

declare(strict_types=1);

//https://solutionfactor.net/blog/2014/02/08/implementing-session-timeout-with-php/

//https://github.com/zendframework/zend-session/blob/master/src/SessionManager.php#L205

//https://github.com/akrabat/rka-slim-session-middleware/blob/master/RKA/Session.php

//https://github.com/relayphp/Relay.Middleware/blob/1.x/src/SessionHeadersHandler.php

// TODO : rememberMe : https://github.com/zendframework/zend-session/blob/master/src/SessionManager.php#L347
//https://github.com/ctidigital/Zend/blob/master/lib/Zend/Session.php#L330

//https://github.com/symfony/symfony/blob/4.0/src/Symfony/Component/HttpFoundation/Session/Storage/NativeSessionStorage.php#L356

//https://github.com/odan/slim-session/blob/master/src/Slim/Session/Adapter/PhpSessionAdapter.php

//https://github.com/juliangut/slim-session-middleware/blob/master/src/SessionMiddleware.php

//https://github.com/zendframework/zend-session/blob/master/src/SessionManager.php

// SEssion + COOKIE : https://github.com/fkooman/php-secookie/blob/master/src/Session.php  +  https://github.com/fkooman/php-secookie/blob/master/src/Cookie.php

//----------- MIDDLEWARE --------------

// avec autorefresh de la session => https://github.com/zendframework/zend-expressive-session/blob/master/src/SessionMiddleware.php
//      https://github.com/zendframework/zend-expressive-session-ext/blob/master/src/PhpSessionPersistence.php
// Avec un autorefresh : https://github.com/adbario/slim-secure-session-middleware/blob/master/src/SessionMiddleware.php#L160

//https://github.com/bryanjhv/slim-session/blob/master/src/Slim/Middleware/Session.php
//https://github.com/akrabat/rka-slim-session-middleware/blob/master/RKA/SessionMiddleware.php
//https://github.com/juliangut/slim-session-middleware/blob/master/src/SessionMiddleware.php

/*
// Session middleware
$app->add(function (Request $request, Response $response, $next) {
    $session = $this->get('session');
    $session->start();
    $response = $next($request, $response);
    $session->save(); //<= ca correspond à .commit()

    return $response;
});
*/

//https://github.com/dannym87/expressive-session-middleware/blob/master/src/Http/SessionMiddleware.php
//https://github.com/dwendrich/expressive-session-middleware/blob/master/src/Middleware/SessionMiddleware.php
//https://github.com/middlewares/aura-session/blob/master/src/AuraSession.php
//https://github.com/middlewares/php-session/blob/master/src/PhpSession.php

//https://github.com/kodus/session/blob/master/src/SessionMiddleware.php
//      avec cookies : https://github.com/kodus/session/blob/master/src/SessionService.php

//avec Cookie pour le refresh de la session : https://github.com/ellipsephp/session-start/blob/master/src/StartSessionMiddleware.php#L151

// comment créer un cookie header : https://github.com/jasny/session-middleware/blob/master/src/SessionMiddleware.php#L207

//-------------------------------------------------------
// Créer un cookie :
// !!!!!!!!!!!!!!!!!!!
// https://github.com/zendframework/zend-http/blob/master/src/Header/SetCookie.php
// https://github.com/guzzle/guzzle/blob/master/src/Cookie/SetCookie.php
//

// TODO : regarder ici : https://github.com/symfony/symfony/blob/3.4/src/Symfony/Component/HttpFoundation/Session/Storage/NativeSessionStorage.php#L130

namespace Chiron\Http\Session;

use InvalidArgumentException;
use LogicException;
use RuntimeException;

/**
 * A central control point for new session segments, PHP session management
 * values, and CSRF token checking.
 */
class SessionManager
{
    /**
     * Incoming cookies from the client, typically a copy of the $_COOKIE
     * superglobal.
     *
     * @var array
     */
    protected $cookies;

    /**
     * Session cookie parameters.
     *
     * @var array
     */
    protected $cookie_params = [];

    /**
     * A callable to invoke when deleting the session cookie. The callable
     * should have the signature ...
     *
     *      function ($cookie_name, $cookie_params)
     *
     * ... and return null.
     *
     * @var callable|null
     *
     * @see setDeleteCookie()
     */
    protected $delete_cookie;

    // @var Chiron\Http\Session\Session
    protected $session;

    /**
     * Constructor.
     *
     * @param CsrfTokenFactory $csrf_token_factory A CSRF token factory.
     * @param array            $cookies            Optional: An array of cookies from the client, typically a
     *                                             copy of $_COOKIE. Empty array by default.
     * @param callable|null    $delete_cookie      Optional: An alternative callable
     *                                             to invoke when deleting the session cookie. Defaults to `null`.
     */
    public function __construct(
        array $cookies = [],
        $delete_cookie = null
    ) {
        $this->cookies = $cookies;
        $this->setDeleteCookie($delete_cookie);
        $this->cookie_params = session_get_cookie_params();
        $this->session = new Session($this);
    }

    public function getSession(): Session
    {
        return $this->session;
    }

    /**
     * Sets the delete-cookie callable.
     *
     * If parameter is `null`, the session cookie will be deleted using the
     * traditional way, i.e. using an expiration date in the past.
     *
     * @param callable|null $delete_cookie The callable to invoke when deleting the
     *                                     session cookie.
     */
    // TODO : https://github.com/zendframework/zend-session/blob/master/src/SessionManager.php#L437
    public function setDeleteCookie($delete_cookie)
    {
        $this->delete_cookie = $delete_cookie;
        if (! $this->delete_cookie) {
            $this->delete_cookie = function (
                $name,
                $params
            ) {
                setcookie(
                    $name,
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain']
                );
            };
        }
    }

    /**
     * Is a session available to be resumed?
     *
     * @return bool
     */
    public function isResumable(): bool
    {
        $name = $this->getName();

        return isset($this->cookies[$name]);
    }

    /**
     * Is the session already started?
     *
     * @return bool
     */
    public function isStarted(): bool
    {
        return session_status() === \PHP_SESSION_ACTIVE;
    }

    /**
     * Starts a new or existing session.
     *
     * @return bool
     */
    public function start(): bool
    {
        return session_start();
    }

    /**
     * Resumes a session, but does not start a new one if there is no
     * existing one.
     *
     * @return bool
     */
    public function resume(): bool
    {
        if ($this->isStarted()) {
            return true;
        }
        if ($this->isResumable()) {
            return $this->start();
        }

        return false;
    }

    /**
     * Clears all session variables.
     *
     * @return null|bool
     */
    public function clear(): ?bool
    {
        return session_unset();
    }

    /**
     * Writes session data and ends the session.
     *
     * @return null|bool
     */
    public function save(): ?bool
    {
        return session_write_close();
    }

    /**
     * Destroys the session entirely.
     *
     * @return bool
     *
     * @see http://php.net/manual/en/function.session-destroy.php
     */
    public function destroy(): bool
    {
        if (! $this->isStarted()) {
            $this->start();
        }
        $name = $this->getName();
        $params = $this->getCookieParams();
        $this->clear();
        $destroyed = session_destroy();
        if ($destroyed) {
            call_user_func($this->delete_cookie, $name, $params);
        }

        return $destroyed;
    }

    // =======================================================================
    //
    // support and admin methods
    //

    /**
     * Sets the session cache expire time.
     *
     * @param int $expire The expiration time in seconds.
     *
     * @return int
     *
     * @see session_cache_expire()
     */
    public function setCacheExpire(int $expire): int
    {
        // TODO : lever une exception si on appael cette fonction alors que la session est déjà démarrée !!! http://php.net/manual/fr/function.session-cache-expire.php
        return session_cache_expire($expire);
    }

    /**
     * Gets the session cache expire time.
     *
     * @return int The cache expiration time in seconds.
     *
     * @see session_cache_expire()
     */
    public function getCacheExpire(): int
    {
        return session_cache_expire();
    }

    /**
     * Sets the session cache limiter value.
     *
     * @param string $limiter The limiter value.
     *
     * @return string
     *
     * @see session_cache_limiter()
     */
    public function setCacheLimiter(string $limiter): string
    {
        // TODO : lever une exception si la valeur de cache_limiter n'est pas dans cette liste : nocache, private, private_no_expire, public
        // TODO : lever une exception si on appael cette fonction alors que la session est déjà démarrée !!!
        return session_cache_limiter($limiter);
    }

    /**
     * Gets the session cache limiter value.
     *
     * @return string The limiter value.
     *
     * @see session_cache_limiter()
     */
    public function getCacheLimiter(): string
    {
        return session_cache_limiter();
    }

    /**
     * Sets the session cookie params.  Param array keys are:.
     *
     * - `lifetime` : Lifetime of the session cookie, defined in seconds.
     *
     * - `path` : Path on the domain where the cookie will work.
     *   Use a single slash ('/') for all paths on the domain.
     *
     * - `domain` : Cookie domain, for example 'www.php.net'.
     *   To make cookies visible on all subdomains then the domain must be
     *   prefixed with a dot like '.php.net'.
     *
     * - `secure` : If TRUE cookie will only be sent over secure connections.
     *
     * - `httponly` : If set to TRUE then PHP will attempt to send the httponly
     *   flag when setting the session cookie.
     *
     * @param array $params The array of session cookie param keys and values.
     *
     * @return null|bool
     *
     * @see session_set_cookie_params()
     */
    public function setCookieParams(array $params): ?bool
    {
        // TODO : lever une exception si la session des déjà démarrée lorsqu'on appelle cette fonction !!!!!!!!!!
        $this->cookie_params = array_merge($this->cookie_params, $params);

        return session_set_cookie_params(
            $this->cookie_params['lifetime'],
            $this->cookie_params['path'],
            $this->cookie_params['domain'],
            $this->cookie_params['secure'],
            $this->cookie_params['httponly']
        );
    }

    /**
     * Set cookie parameters.
     *
     * @see http://php.net/manual/en/function.session-set-cookie-params.php
     *
     * @param int    $lifetime The lifetime of the cookie in seconds.
     * @param string $path     The path where information is stored.
     * @param string $domain   The domain of the cookie.
     * @param bool   $secure   The cookie should only be sent over secure connections.
     * @param bool   $httpOnly The cookie can only be accessed through the HTTP protocol.
     */
    public function setCookieParams2(int $lifetime, string $path = null, string $domain = null, bool $secure = false, bool $httpOnly = false): void
    {
        session_set_cookie_params($lifetime, $path, $domain, $secure, $httpOnly);
    }

    /**
     * Get cookie parameters.
     *
     * @see http://php.net/manual/en/function.session-get-cookie-params.php
     *
     * @return array
     */
    public function getCookieParams(): array
    {
        return $this->cookie_params;
        //return session_get_cookie_params();
    }

    /**
     * Gets the current session id.
     *
     * @return string
     */
    public function getId(): string
    {
        return session_id();
    }

    /**
     * Sets the session ID.
     *
     * @param string $id
     *
     * @throws \LogicException
     */
    public function setId(string $id): void
    {
        if ($this->isStarted()) {
            throw new LogicException('Cannot change the ID of an active session, to change the session ID call regenerateId()');
        }
        session_id($id);
    }

    /**
     * Regenerates and replaces the current session id;.
     *
     * @return bool True if regeneration worked, false if not.
     */

    /**
     * Regenerate id.
     *
     * Regenerate the session ID, using session save handler's
     * native ID generation Can safely be called in the middle of a session.
     *
     * @param bool $deleteOldSession
     *
     * @return bool
     */
    public function regenerateId(bool $deleteOldSession = true): bool
    {
        /*
        if ($this->sessionExists()) {
            session_regenerate_id((bool) $deleteOldSession);
        }
        return $this;
        */
        if ($this->isStarted()) {
            return session_regenerate_id($deleteOldSession);
        }

        return false;
    }

    /**
     * Sets the current session name.
     *
     * @param string $name The session name to use.
     *
     * @return string
     *
     * @see session_name()
     */
    public function setName(string $name): string
    {
        if ($this->isStarted()) {
            throw new LogicException('Cannot change the name of an active session');
        }

        if (! preg_match('/^[a-zA-Z0-9]+$/', $name)) {
            throw new InvalidArgumentException('Session name provided contains invalid characters; must be alphanumeric only and cannot be empty');
        }

        if (! preg_match('/.*[a-zA-Z]+.*/', $name)) {
            throw new InvalidArgumentException('Session name cannot be a numeric');
        }

        return session_name($name);
    }

    /**
     * Returns the current session name.
     *
     * @return string
     */
    public function getName(): string
    {
        return session_name();
    }

    /**
     * Sets the session save path.
     *
     * @param string $path The new save path.
     *
     * @return string
     *
     * @see session_save_path()
     */
    public function setSavePath(string $path): string
    {
        if (! is_writable($path)) {
            throw new RuntimeException("Session save path : '{$path}' is not writable.");
        }

        return session_save_path($path);
    }

    /**
     * Gets the session save path.
     *
     * @return string
     *
     * @see session_save_path()
     */
    public function getSavePath(): string
    {
        return session_save_path();
    }

    /**
     * Sets session.* ini variables.
     *
     * For convenience we omit 'session.' from the beginning of the keys.
     * Explicitly ignores other ini keys.
     *
     * @param array $options Session ini directives array(key => value)
     *
     * @see http://php.net/session.configuration
     */
    public function setOptions(array $options): void
    {
        if (headers_sent() || \PHP_SESSION_ACTIVE === session_status()) {
            return;
        }
        $validOptions = array_flip([
            'cache_limiter', 'cache_expire', 'cookie_domain', 'cookie_httponly',
            'cookie_lifetime', 'cookie_path', 'cookie_secure',
            'gc_divisor', 'gc_maxlifetime', 'gc_probability', 'auto_start',
            'lazy_write', 'name', 'referer_check', 'save_path', 'save_handler',
            'serialize_handler', 'use_strict_mode', 'use_cookies',
            'use_only_cookies', 'use_trans_sid', 'upload_progress.enabled',
            'upload_progress.cleanup', 'upload_progress.prefix', 'upload_progress.name',
            'upload_progress.freq', 'upload_progress.min_freq',
            'sid_length', 'sid_bits_per_character', 'trans_sid_hosts', 'trans_sid_tags',
        ]);
        foreach ($options as $key => $value) {
            if (isset($validOptions[$key])) {
                ini_set('session.' . $key, (string) $value);
            }
        }
    }

    public function getOptions(): array
    {
        $config = [];
        foreach (ini_get_all('session') as $key => $value) {
            $config[substr($key, 8)] = $value['local_value'];
        }

        return $config;
    }

    public function regenerateId2($destroy = false, $lifetime = null): bool
    {
        // Cannot regenerate the session ID for non-active sessions.
        if (\PHP_SESSION_ACTIVE !== session_status()) {
            return false;
        }
        if (headers_sent()) {
            return false;
        }
        if (null !== $lifetime) {
            ini_set('session.cookie_lifetime', $lifetime);
        }
        if ($destroy) {
            $this->metadataBag->stampNew();
        }
        $isRegenerated = session_regenerate_id($destroy);
        // The reference to $_SESSION in session bags is lost in PHP7 and we need to re-create it.
        // @see https://bugs.php.net/bug.php?id=70013
        $this->loadSession();

        return $isRegenerated;
    }

    /**
     * Destroy all session data.
     *
     * @return $this
     */
    public static function destroy2()
    {
        if (self::status() === \PHP_SESSION_ACTIVE) {
            //session_unset();
            self::flush();
            session_destroy();
            session_write_close();

            // TODO : utiliser 42000 comme nombre au lieu de -1heure => https://github.com/odan/slim-session/blob/master/src/Slim/Session/Adapter/PhpSessionAdapter.php#L39
            // delete the session cookie => lifetime = -1h (60 * 60)
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 3600,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }
        }

        //return $this->clear(false);
    }

    public function has(string $name): bool
    {
        if (empty($_SESSION)) {
            return false;
        }

        return array_key_exists($name, $_SESSION);
    }

    public function get(string $name, $default = null)
    {
        return $this->has($name)
            ? $_SESSION[$name]
            : $default;
    }

    public function set(string $name, $value)
    {
        $_SESSION[$name] = $value;
    }

    public function replace(array $values): void
    {
        $_SESSION = array_replace_recursive($_SESSION, $values);
    }

    public function remove(string $name): void
    {
        // TODO : vérifier si un has() est necessaire à faire avant le unset.
        unset($_SESSION[$name]);
        /*
        if (array_key_exists($key, $_SESSION)) {
            unset($_SESSION[$key]);
        }*/
    }

    public function clear2(): void
    {
        $_SESSION = [];
    }

    public function count(): int
    {
        return count($_SESSION);
    }

    public function destroy3(): bool
    {
        $this->clear();
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                $this->getName(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        if ($this->isStarted()) {
            session_destroy();
            session_unset();
        }
        session_write_close();

        return true;
    }

    public static function destroy4()
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /**
     * Register Save Handler with ext/session.
     *
     * Register the save handler for session.
     *
     * @param \SaveHandlerInterface $saveHandler
     *
     * @return bool
     */
    protected function registerSaveHandler(\SaveHandlerInterface $saveHandler): bool
    {
        return session_set_save_handler($saveHandler);
    }
}
