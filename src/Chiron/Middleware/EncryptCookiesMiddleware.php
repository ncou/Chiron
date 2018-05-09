<?php

declare(strict_types=1);

namespace Chiron\Middleware;

use Chiron\CookiesManager;
use Chiron\CryptManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class EncryptCookiesMiddleware implements MiddlewareInterface
{
    /**
     * The defuse encryption keu.
     *
     * @var \Defuse\Crypto\Key
     */
    private $password;

    /**
     * The names of the cookies which bypass encryption.
     *
     * @var array
     */
    private $bypassed;

    private $crypter;

    /**
     * Set up a encrypt cookie middleware with the given defuse key and an array
     * of bypassed cookie names.
     *
     * @param \Defuse\Crypto\Key $password
     * @param array              $bypassed
     */
    public function __construct(string $password, array $bypassed = [])
    {
        $this->password = $password;
        $this->bypassed = $bypassed;
        $this->crypter = new CryptManager();
    }

    /**
     * Start the session, delegate the request processing and add the session
     * cookie to the response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $this->withDecryptedCookies($request);
        $response = $handler->handle($request);

        return $this->withEncryptedCookies($response);
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
        return $this->crypter->encrypt($value, $this->password);
    }

    /**
     * Decrypt the given value using the key. Default to blank string when the
     * key is wrong or the cypher text has been modified.
     *
     * @param string $value
     *
     * @return string
     */
    private function decrypt(string $value): string
    {
        try {
            return $this->crypter->decrypt($value, $this->password);
        } catch (\Throwable $t) {
            // @TODO : Add a silent log message if there is an error in the cookie decrypt function.
            return '';
        }
    }

    /**
     * Decrypt the non bypassed cookie values attached to the given request
     * and return a new request with those values.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ServerRequestInterface
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
     * Encrypt the non bypassed cookie values attached to the given response
     * and return a new response with those values.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function withEncryptedCookies2(ResponseInterface $response): ResponseInterface
    {
        $cookies = (SetCookies::fromResponse($response))->getAll();
        foreach ($cookies as $cookie) {
            $name = $cookie->getName();
            if (in_array($name, $this->bypassed)) {
                continue;
            }
            $response = FigResponseCookies::modify($response, $name, function (SetCookie $cookie) {
                $value = $cookie->getValue();
                $encrypted = $this->encrypt($value);

                return $cookie->withValue($encrypted);
            });
        }

        return $response;
    }

    /**
     * Encode cookies from a response's Set-Cookie header.
     *
     * @param \Psr\Http\Message\ResponseInterface $response The response to encode cookies in.
     *
     * @return \Psr\Http\Message\ResponseInterface Updated response with encoded cookies.
     */
    protected function withEncryptedCookies(ResponseInterface $response): ResponseInterface
    {
        /*
        $cookies = CookieCollection::createFromHeader($response->getHeader('Set-Cookie'));
        $header = [];
        foreach ($cookies as $cookie) {
            if (in_array($cookie->getName(), $this->cookieNames, true)) {
                $value = $this->_encrypt($cookie->getValue(), $this->cipherType);
                $cookie = $cookie->withValue($value);
            }
            $header[] = $cookie->toHeaderValue();
        }
        return $response->withHeader('Set-Cookie', $header);
*/

        $cookiesManager = new CookiesManager();
        //$cookies = CookiesManager::parseHeaders($response->getHeader('Set-Cookie'));

        $cookies = CookiesManager::parseSetCookieHeader($response->getHeader('Set-Cookie'));

        // remove all the cookies
        $response = $response->withoutHeader('Set-Cookie');

        $header = [];
        foreach ($cookies as $name => $cookie) {
            if (! in_array($name, $this->bypassed)) {
                $cookie['value'] = $this->crypter->encrypt($cookie['value'], $this->password);
            }

            //$cookiesManager->set($name, $value);
            // add again all the cookies (and some are now encrypted)
            $response = $response->withAddedHeader('Set-Cookie', CookiesManager::toHeader($name, $cookie));
        }

        return $response;
    }
}
