<?php

declare(strict_types=1);

namespace Chiron\Http\Middleware;

use Chiron\Http\Session\SessionManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

//https://github.com/davidecesarano/Embryo-Session/blob/master/Embryo/Session/Middleware/SessionMiddleware.php

class SessionManagerMiddleware implements MiddlewareInterface
{
    /** @var array Default settings */
    protected $settings = [
        // Session cookie settings
        'name'           => 'CHRSESSIONID',
        // session lifetime in seconds
        'lifetime'       => 30 * 60,
        'path'           => '/',
        'domain'         => null,
        'secure'         => false,
        'httponly'       => true,
        // Path where session files are stored, PHP's default path will be used if set null
        'save_path'      => null,
        // Session cache limiter
        // TODO : on devait aussi le passer à null pour s'assurer que PHP utilise la valeur définie par défaut dans le fichier ini
        'cache_limiter'  => 'nocache',
    ];

    /**
     * Constructor.
     *
     * @param array $settings Session settings
     */
    public function __construct(array $settings = [])
    {
        $this->settings = array_merge($this->settings, $settings);
    }

    /**
     * Process a server request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // TODO : lever une exception si la session est déjà démarrée, cela évitera de faire un "if isStarted" dans la méthode $this->startSession()
        $sessionManager = new SessionManager($request->getCookieParams());

        if (! $sessionManager->isStarted()) {
            $this->configAndStartSession($sessionManager);
        }

        $request = $request->withAttribute(SessionManager::class, $sessionManager);

        return $handler->handle($request);
    }

    private function configAndStartSession(SessionManager $sessionManager)
    {
        $settings = $this->settings;

        // TODO : utiliser la méthode : $sessionManager->setOptions([xxx=>xxx]);
        // Enable strict mode
        ini_set('session.use_strict_mode', '1');
        // Use cookies and only cookies to store session id
        ini_set('session.use_cookies', '1');
        ini_set('session.use_only_cookies', '1');
        // Disable inserting session id into links automatically
        ini_set('session.use_trans_sid', '0');
        // Set session id strength - Since PHP version >= 7.1
        ini_set('session.sid_length', '128');
        // Set number of seconds after which data will be seen as garbage
        if ($settings['lifetime'] > 0) {
            ini_set('session.gc_maxlifetime', (string) $settings['lifetime']);
        }
        // Set path where session cookies are saved
        if (! is_null($settings['save_path'])) {
            $sessionManager->setSavePath($settings['save_path']);
        }
        // Set session cache limiter
        $sessionManager->setCacheLimiter($settings['cache_limiter']);
        // Set session cookie name
        $sessionManager->setName($settings['name']);
        // Set session cookie parameters
        $current = session_get_cookie_params();
        $lifetime = (int) ($settings['lifetime'] ?: $current['lifetime']);
        $path = $settings['path'] ?: $current['path'];
        $domain = $settings['domain'] ?: $current['domain'];
        $secure = (bool) $settings['secure'];
        $httponly = (bool) $settings['httponly'];
        session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);

        $sessionManager->start();
    }
}
