<?php

declare(strict_types=1);

namespace Chiron\Http\Middleware;

use Chiron\CryptEngine;
use Chiron\Http\Cookie\CookiesManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class EncryptCookiesMiddleware implements MiddlewareInterface
{
    /**
     * The encryption key.
     *
     * @var string
     */
    private $password;

    /**
     * The names of the cookies which bypass encryption.
     *
     * @var array
     */
    private $bypassed;

    /**
     * Set up a encrypt cookie middleware with the given password key and an array of bypassed cookie names.
     *
     * @param string $password
     * @param array  $bypassed
     */
    public function __construct(string $password, array $bypassed = [])
    {
        $this->password = $password;
        $this->bypassed = $bypassed;
    }

    /**
     * Start the session, delegate the request processing and add the session cookie to the response.
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $this->withDecryptedCookies($request);
        $response = $handler->handle($request);

        return $this->withEncryptedCookies($response);
    }

    /**
     * Decrypt the non bypassed cookie values attached to the given request and return a new request with those values.
     *
     * @param ServerRequestInterface $request
     *
     * @return ServerRequestInterface
     */
    private function withDecryptedCookies(ServerRequestInterface $request): ServerRequestInterface
    {
        $cookies = $request->getCookieParams();

        $decrypted = [];
        foreach ($cookies as $name => $value) {
            $decrypted[$name] = in_array($name, $this->bypassed) ? $value : $this->decrypt($value);
        }

        return $request->withCookieParams($decrypted);
    }

    /**
     * Encode cookies from a response's Set-Cookie header.
     *
     * @param ResponseInterface $response The response to encode cookies in.
     *
     * @return ResponseInterface Updated response with encoded cookies.
     */
    protected function withEncryptedCookies(ResponseInterface $response): ResponseInterface
    {
        //$cookiesManager = new CookiesManager();
        //$cookies = CookiesManager::parseHeaders($response->getHeader('Set-Cookie'));

        $cookies = CookiesManager::parseSetCookieHeader($response->getHeader('Set-Cookie'));

        // remove all the cookies
        $response = $response->withoutHeader('Set-Cookie');

        //$header = [];
        foreach ($cookies as $name => $cookie) {
            if (! in_array($name, $this->bypassed)) {
                $cookie['value'] = $this->encrypt($cookie['value']);
            }

            //$cookiesManager->set($name, $value);
            // add again all the cookies (and some are now encrypted)
            $response = $response->withAddedHeader('Set-Cookie', CookiesManager::toHeader($name, $cookie));
        }

        return $response;
    }

    /**
     * Encrypt the given value using the key.
     *
     * @param string $value
     *
     * @return string
     */
    private function encrypt(string $value): string
    {
        return CryptEngine::encrypt($value, $this->password);
    }

    /**
     * Decrypt the given value using the key.
     * Return default to blank string when the key is wrong or the cypher text has been modified.
     *
     * @param string $value
     *
     * @return string
     */
    private function decrypt(string $value): string
    {
        try {
            return CryptEngine::decrypt($value, $this->password);
        } catch (\Throwable $t) {
            // @TODO : Add a silent log message if there is an error in the cookie decrypt function.
            return '';
        }
    }
}
