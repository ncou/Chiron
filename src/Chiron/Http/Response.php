<?php

declare(strict_types=1);

namespace Chiron\Http;

// cookies :     https://github.com/michaelbromley/drawACatApp/tree/master/api/Slim/Http

//https://github.com/Guzzle3/http/blob/master/Message/Response.php
//https://github.com/guzzle/guzzle3/blob/master/src/Guzzle/Http/Message/Response.php

//https://github.com/symfony/http-foundation/blob/master/Response.php

//https://github.com/stratifyphp/http/blob/master/src/Response/SimpleResponse.php

// TODO : regarder les interfaces ici pour voir si la classe est PSR7 compatible https://github.com/php-fig/http-message/tree/master/src
//https://github.com/koolkode/http/blob/master/src/HttpResponse.php

// TODO : example : https://github.com/narrowspark/framework/blob/master/src/Viserio/Component/Http/Response.php

//https://github.com/symfony/http-foundation/blob/master/Response.php

// https://github.com/phly/http/tree/master/src
//https://github.com/guzzle/psr7/blob/master/src
//https://github.com/zendframework/zend-diactoros/tree/master/src

//https://github.com/cakephp/cakephp/blob/master/src/Http/Response.php
//https://github.com/symfony/http-foundation/blob/master/Response.php

use Nyholm\Psr7\Response as ResponsePsr7;

use Chiron\Http\Body;

class Response extends ResponsePsr7
{

    const FORMAT_URLENCODED = "URLENCODED";
    const FORMAT_JSON = "JSON";
    const FORMAT_XML = "XML";


    // TODO : il faudrait pas implémenter une méthode clone avec les objets genre header ou cookies ????     https://github.com/slimphp/Slim/blob/3.x/Slim/Http/Response.php#L147
    // TODO : les cookies ne semble pas avoir leur place ici !!!!!!!!!!
    private $cookies = [];

    // https://github.com/guzzle/guzzle3/blob/master/src/Guzzle/Http/Message/Response.php#L99
    /** @var array Cacheable response codes (see RFC 2616:13.4) */
    protected static $cacheResponseCodes = [200, 203, 206, 300, 301, 410]; // 200, 203, 300, 301, 302, 404, 410
    // TODO : regarder ici la liste : https://github.com/micheh/psr7-cache/blob/master/src/CacheUtil.php#L289

    // TODO : vérifier si on garde l'initialisation du ProtocolVersion en trant que paramétre du constructeur
    // TODO : virer la partie "reason" du constructeur ?????
    //@param string|resource|StreamInterface $body Stream identifier and/or actual stream resource

    //public function __construct($status = 200, $body = 'php://temp', $reason = '', $version = '1.1', array $headers = [])
    public function __construct(int $status = 200, array $headers = [], $body = null, string $version = '1.1', $reason = null)
    {
        parent::__construct($status, $headers, $body, $version, $reason);
    }

    /**
     * Return the reason phrase by code.
     *
     * @param $code
     *
     * @return string
     */
    /*
    // NOT A PSR7 FUNCTION
    public static function getReasonPhraseByCode($code): string
    {
        return self::$phrases[$code] ?? '';
    }
    */

    /**
     * Set a valid status code.
     *
     * @param int $code
     *
     * @throws InvalidArgumentException on an invalid status code.
     */
    // NOT A PSR7 FUNCTION
    //https://github.com/phly/http/blob/master/src/Response.php#L167
    /*
    protected function setStatusCode($code)
    {
        if (! is_numeric($code)
            || is_float($code)
            || $code < static::MIN_STATUS_CODE_VALUE
            || $code > static::MAX_STATUS_CODE_VALUE
        ) {
            throw new InvalidArgumentException(sprintf(
                'Invalid status code "%s"; must be an integer between %d and %d, inclusive',
                (is_scalar($code) ? $code : gettype($code)),
                static::MIN_STATUS_CODE_VALUE,
                static::MAX_STATUS_CODE_VALUE
            ));
        }
        $this->statusCode = $code;
    }*/

    /*******************************************************************************
     * Body
     ******************************************************************************/

    /**
     * Write data to the response body.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * Proxies to the underlying stream and writes the provided data to it.
     *
     * @param string $data
     *
     * @return $this
     */
    public function write(string $data)
    {
        $this->getBody()->write($data);

        return $this;
    }

    //--------------------------
    // https://github.com/swoft-cloud/framework/blob/master/src/Base/Response.php

    /**
     * @var string
     */
    //protected $charset = 'utf-8';

    /**
     * Return an instance with the specified charset content type.
     *
     * @param $charset
     *
     * @return static
     */
    /*
    public function withCharset($charset): self
    {
        $clone = clone $this;
        $clone->withAddedHeader('Content-Type', sprintf('charset=%s', $charset));
        return $clone;
    }
    */

    /**
     * @return string
     */
    /*
    public function getCharset(): string
    {
        return $this->charset;
    }
    */
    /**
     * @param string $charset
     *
     * @return Response
     */
    /*
    public function setCharset(string $charset): Response
    {
        $this->charset = $charset;
        return $this;
    }
    */

    //https://github.com/cakephp/cakephp/blob/master/src/Http/Response.php#L1170
    /**
     * The charset the response body is encoded with.
     *
     * @var string
     */
    //protected $_charset = 'UTF-8';
    /**
     * Sets the response charset
     * if $charset is null the current charset is returned.
     *
     * @param string|null $charset Character set string.
     *
     * @return string Current charset
     *
     * @deprecated 3.5.0 Use getCharset()/withCharset() instead.
     */
    /*
    public function charset($charset = null)
    {
        if ($charset === null) {
            return $this->_charset;
        }
        $this->_charset = $charset;
        $this->_setContentType();
        return $this->_charset;
    }*/
    /**
     * Returns the current charset.
     *
     * @return string
     */
    /*
    public function getCharset()
    {
        return $this->_charset;
    }*/
    /**
     * Get a new instance with an updated charset.
     *
     * @param string $charset Character set string.
     *
     * @return static
     */
    /*
    public function withCharset($charset)
    {
        $new = clone $this;
        $new->_charset = $charset;
        $new->_setContentType();
        return $new;
    }*/

    /**
     * Refreshes the current page.
     * The effect of this method call is the same as the user pressing the refresh button of his browser
     * (without re-posting data).
     *
     * In a controller action you may use this method like this:
     *
     * ```php
     * return Yii::$app->getResponse()->refresh();
     * ```
     *
     * @param string $anchor the anchor that should be appended to the redirection URL.
     *                       Defaults to empty. Make sure the anchor starts with '#' if you want to specify it.
     *
     * @return Response the response object itself
     */
    /*
    public function refresh($anchor = '')
    {
        return $this->redirect(Yii::$app->getRequest()->getUrl() . $anchor);
    }*/

    /**
     * Sets the response status code based on the exception.
     *
     * @param \Exception|\Error $e the exception object.
     *
     * @throws InvalidArgumentException if the status code is invalid.
     *
     * @return $this the response object itself
     *
     * @since 2.0.12
     */
    //https://github.com/yiisoft/yii2/blob/master/framework/web/Response.php#L303
    /*
    public function setStatusCodeByException($e)
    {
        if ($e instanceof HttpException) {
            $this->setStatusCode($e->statusCode);
        } else {
            $this->setStatusCode(500);
        }
        return $this;
    }*/

    /**
     * Is the response OK?
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isOk(): bool
    {
        return $this->getStatusCode() === 200;
    }

    /**
     * Is the response empty?
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return in_array($this->getStatusCode(), [204, 304]);
    }

    /**
     * Is this response empty?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    /*
    public function isEmpty()
    {
        return in_array($this->getStatusCode(), [204, 205, 304]);
    }*/

    /**
     * @return bool whether this response is empty
     */
    /*
    public function getIsEmpty()
    {
        return in_array($this->getStatusCode(), [201, 204, 304]);
    }*/

    /**
     * Is the response a redirect of some form?
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isRedirect(): bool
    {
        return in_array($this->getStatusCode(), [301, 302, 303, 307, 308]);
    }

    /**
     * Is this response a redirect?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    /*
    public function isRedirect()
    {
        return in_array($this->getStatusCode(), [301, 302, 303, 307]);
    }*/

    /**
     * Is response invalid?
     * Note: This method is not part of the PSR-7 standard.
     *
     * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     *
     * @return bool
     */
    public function isInvalid(): bool
    {
        return $this->getStatusCode() < 100 || $this->getStatusCode() >= 600;
    }

    /**
     * Is response informative?
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isInformational(): bool
    {
        return $this->getStatusCode() >= 100 && $this->getStatusCode() < 200;
    }

    /**
     * Is response successful?
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->getStatusCode() >= 200 && $this->getStatusCode() < 300;
    }

    /**
     * Checks if HTTP Status code is Successful (2xx | 304).
     *
     * @return bool
     */
    /*
    public function isSuccessful()
    {
        return ($this->getStatusCode() >= 200 && $this->getStatusCode() < 300) || $this->getStatusCode() == 304;
    }*/

    /**
     * Is the response a redirect?
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isRedirection(): bool
    {
        return $this->getStatusCode() >= 300 && $this->getStatusCode() < 400;
    }

    /**
     * Is there a client error?
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isClientError(): bool
    {
        return $this->getStatusCode() >= 400 && $this->getStatusCode() < 500;
    }

    /**
     * Was there a server side error?
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isServerError(): bool
    {
        return $this->getStatusCode() >= 500 && $this->getStatusCode() < 600;
    }

    /**
     * Checks if HTTP Status code is Server OR Client Error (4xx or 5xx).
     *
     * @return bool
     */
    public function isError(): bool
    {
        return $this->isClientError() || $this->isServerError();
    }

    /**
     * Is the response forbidden?
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isForbidden(): bool
    {
        return $this->getStatusCode() === 403;
    }

    /**
     * Is the response a not found error?
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isNotFound(): bool
    {
        return $this->getStatusCode() === 404;
    }

    /**
     * Is the response a method not allowed error?
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isMethodNotAllowed(): bool
    {
        return $this->getStatusCode() === 405;
    }

    /**
     * @return array the formatters that are supported by default
     */
    //https://github.com/yiisoft/yii2/blob/master/framework/web/Response.php#L1005
    /*
    protected function defaultFormatters()
    {
        return [
            self::FORMAT_HTML => [
                'class' => 'yii\web\HtmlResponseFormatter',
            ],
            self::FORMAT_XML => [
                'class' => 'yii\web\XmlResponseFormatter',
            ],
            self::FORMAT_JSON => [
                'class' => 'yii\web\JsonResponseFormatter',
            ],
            self::FORMAT_JSONP => [
                'class' => 'yii\web\JsonResponseFormatter',
                'useJsonp' => true,
            ],
        ];
    }*/

    /*******************************************************************************
     * Cookie Section
     ******************************************************************************/

    /**
     * 添加cookie
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param string $key
     * @param string $value
     * @param int    $expire
     * @param string $path
     * @param string $domain
     */
    public function addCookie($key, $value, $expire = 0, $path = '/', $domain = '')
    {
        $this->swooleResponse->cookie($key, $value, $expire, $path, $domain);
    }

    // TODO : regarder comment c'est fait ici : https://github.com/dflydev/dflydev-fig-cookies/blob/master/src/Dflydev/FigCookies/SetCookie.php
    // TODO : transformer cela en header classique ???? =>   https://stackoverflow.com/questions/35257522/slim-3-framework-cookies
    /**
     * @param Response $response
     * @param string   $key
     * @param string   $value
     *
     * @return Response
     */
    /*
        public function deleteCookie(Response $response, $key)
        {
            $cookie = urlencode($key).'='.
                urlencode('deleted').'; expires=Thu, 01-Jan-1970 00:00:01 GMT; Max-Age=0; path=/; secure; httponly';
            $response = $response->withAddedHeader('Set-Cookie', $cookie);
            return $response;
        }
    Good answer. However, when deleting cookie, use empty text as value for deleted cookie. Your own method getCookieValue will return null when trying to get a cookie deleted by deleteCookie. More consistent (for me at least).

    */
    /**
     * @param Response $response
     * @param string   $cookieName
     * @param string   $cookieValue
     *
     * @return Response
     */
    /*
        public function addCookie(Response $response, $cookieName, $cookieValue)
        {
            $expirationMinutes = 10;
            $expiry = new \DateTimeImmutable('now + '.$expirationMinutes.'minutes');
            $cookie = urlencode($cookieName).'='.
                urlencode($cookieValue).'; expires='.$expiry->format(\DateTime::COOKIE).'; Max-Age=' .
                $expirationMinutes * 60 . '; path=/; secure; httponly';
            $response = $response->withAddedHeader('Set-Cookie', $cookie);
            return $response;
        }
    */
    /**
     * @param Request $request
     * @param string  $cookieName
     *
     * @return string
     */
    /*
        public function getCookieValue(Request $request, $cookieName)
        {
            $cookies = $request->getCookieParams();
            return isset($cookies[$cookieName]) ? $cookies[$cookieName] : null;
        }
    */

    //* Note: This method is not part of the PSR-7 standard.
    // TODO : voir ou on positionne cette méthode !!!!!!!!!!!!!!!! ca ne semble pas avoir sa place dans la classe Reponse ou dans la classe Message
    public function setCookie($name, $value, $expire = null, $path = '/', $domain = '', $secure = false, $httpOnly = false)
    {
        $name = (string) $name;

        if (! is_null($expire)) {
            if (is_numeric($expire)) {
                $expire = (int) $expire;
            } else {
                $expire = strtotime($expire);
                if (false === $expire || -1 == $expire) {
                    throw new InvalidArgumentException('The cookie expire parameter is not valid.');
                }
            }
        }

        $this->cookies[$name] = [
          'name'     => $name,
          'value'    => $value,
          'expire'   => $expire,
          'path'     => $path,
          'domain'   => $domain,
          'secure'   => (bool) $secure,
          'httpOnly' => (bool) $httpOnly,
        ];

        return $this;
    }

    //*********************************
    // COOKIES ************************
    //*********************************

    //https://github.com/symfony/psr-http-message-bridge/blob/master/Factory/HttpFoundationFactory.php
    //https://github.com/symfony/http-foundation/blob/master/Cookie.php

    /**
     * Creates a Cookie instance from a cookie string.
     * Note: This method is not part of the PSR-7 standard.
     *
     * Some snippets have been taken from the Guzzle project: https://github.com/guzzle/guzzle/blob/5.3/src/Cookie/SetCookie.php#L34
     *
     * @param string $cookie
     *
     * @throws \InvalidArgumentException
     *
     * @return Cookie
     */
    private function createCookie($cookie)
    {
        foreach (explode(';', $cookie) as $part) {
            $part = trim($part);
            $data = explode('=', $part, 2);
            $name = $data[0];
            $value = isset($data[1]) ? trim($data[1], " \n\r\t\0\x0B\"") : null;
            if (! isset($cookieName)) {
                $cookieName = $name;
                $cookieValue = $value;
                continue;
            }
            if ('expires' === strtolower($name) && null !== $value) {
                $cookieExpire = new \DateTime($value);
                continue;
            }
            if ('path' === strtolower($name) && null !== $value) {
                $cookiePath = $value;
                continue;
            }
            if ('domain' === strtolower($name) && null !== $value) {
                $cookieDomain = $value;
                continue;
            }
            if ('secure' === strtolower($name)) {
                $cookieSecure = true;
                continue;
            }
            if ('httponly' === strtolower($name)) {
                $cookieHttpOnly = true;
                continue;
            }
        }
        if (! isset($cookieName)) {
            throw new \InvalidArgumentException('The value of the Set-Cookie header is malformed.');
        }

        return new Cookie(
            $cookieName,
            $cookieValue,
            isset($cookieExpire) ? $cookieExpire : 0,
            isset($cookiePath) ? $cookiePath : '/',
            isset($cookieDomain) ? $cookieDomain : null,
            isset($cookieSecure),
            isset($cookieHttpOnly)
        );
    }

    /*******************************************************************************
     * Response Helper
     ******************************************************************************/

    /**
     * Redirect.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * This method prepares the response object to return an HTTP Redirect
     * response to the client.
     *
     * @param string|UriInterface $url    The redirect destination.
     * @param int|null            $status The redirect HTTP status code.
     *
     * @return static
     */
    // TODO : vérifier ce code pour gérer le cas du 308 et 307 pour les redirections avec une méthode POST : https://github.com/middlewares/redirect/blob/master/src/Redirect.php#L89
    // TODO : utiliser une classe RedirectResponse et ajouter un body avec un lien hypertext (cf spec : https://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html#sec10.3.2), plus gestion du cache pour les redirections 301 : cf les classes Symfony.
    public function withRedirect($url, $status = null)
    {
        $responseWithRedirect = $this->withHeader('Location', (string) $url);
        if (is_null($status) && $this->getStatusCode() === 200) {
            $status = 302;
        }
        if (! is_null($status)) {
            $responseWithRedirect = $responseWithRedirect->withStatus($status);
        }

        $urlHtml = htmlentities($url);
        $responseWithRedirect->getBody()->write('You are being redirected to <a href="' . $urlHtml . '">' . $urlHtml . '</a>', 'text/html');

        return $responseWithRedirect;
    }

    /**
     * Json.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * This method prepares the response object to return an HTTP Json
     * response to the client.
     *
     * @param mixed $data            The data
     * @param int   $status          The HTTP status code.
     * @param int   $encodingOptions Json encoding options
     *
     * @throws \RuntimeException
     *
     * @return static
     */
    //https://github.com/zendframework/zend-diactoros/blob/master/src/Response/JsonResponse.php
    //TODO : faire un clone de la réponse et retourner ce clone : https://github.com/slimphp/Slim/blob/c9a768c5a062c5f1aaa0a588d7bb90e8ce18bfd6/Slim/Http/Response.php#L346

    //https://github.com/symfony/http-foundation/blob/master/JsonResponse.php
    // Encode <, >, ', &, and " characters in the JSON, making it also safe to be embedded into HTML.
    // Encode <, >, ', &, and " for RFC4627-compliant JSON, which may also be embedded into HTML.
    // 15 === JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    /*
    const DEFAULT_ENCODING_OPTIONS = 15;
    protected $encodingOptions = self::DEFAULT_ENCODING_OPTIONS;
*/
    //TODO : à renommer en "writeJson()" ?????
    public function withJson($data, $status = null, $encodingOptions = 79)
    {
        // default encodingOptions is 79 => JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES

        $response = $this->withBody(new Body('php://temp', 'r+'));
        $response->stream->write($json = json_encode($data, $encodingOptions));

        // Ensure that the json encoding passed successfully
        if ($json === false) {
            throw new \RuntimeException(json_last_error_msg(), json_last_error());
        }
        $responseWithJson = $response->withHeader('Content-Type', 'application/json;charset=utf-8');
        if (isset($status)) {
            return $responseWithJson->withStatus($status);
        }

        return $responseWithJson;
    }

    /**
     * Determine if the given content should be turned into JSON.
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param mixed $content
     *
     * @return bool
     */
    protected function shouldBeJson($content)
    {
        return $content instanceof Arrayable ||
               $content instanceof Jsonable ||
               $content instanceof ArrayObject ||
               $content instanceof JsonSerializable ||
               is_array($content);
    }

    /**
     * Morph the given content into JSON.
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param mixed $content
     *
     * @return string
     */
    protected function morphToJson($content)
    {
        if ($content instanceof Jsonable) {
            return $content->toJson();
        } elseif ($content instanceof Arrayable) {
            return json_encode($content->toArray());
        }

        return json_encode($content);
    }

    /**
     * Add a cookie to the response.
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param \Symfony\Component\HttpFoundation\Cookie|mixed $cookie
     *
     * @return $this
     */
    public function cookie($cookie)
    {
        return call_user_func_array([$this, 'withCookie'], func_get_args());
    }

    /**
     * Add a cookie to the response.
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param \Symfony\Component\HttpFoundation\Cookie|mixed $cookie
     *
     * @return $this
     */
    public function withCookie($cookie)
    {
        if (is_string($cookie) && function_exists('cookie')) {
            $cookie = call_user_func_array('cookie', func_get_args());
        }
        $this->headers->setCookie($cookie);

        return $this;
    }

    //*************************************************************

    /**
     * Check if the response can be cached based on the response headers.
     *
     * @return bool Returns TRUE if the response can be cached or false if not
     */
    //https://github.com/guzzle/guzzle3/blob/master/src/Guzzle/Http/Message/Response.php#L762
    // TODO : regarder ici pour simplifier ce calcul : https://github.com/symfony/symfony/blob/master/src/Symfony/Component/HttpFoundation/Response.php#L515
    // TODO : autre exemple : https://github.com/micheh/psr7-cache/blob/master/src/CacheUtil.php#L289
    // TODO : renommer en isCacheable() ????
    public function canCache()
    {
        // Check if the response is cacheable based on the code
        if (! in_array((int) $this->getStatusCode(), self::$cacheResponseCodes)) {
            return false;
        }
        // Make sure a valid body was returned and can be cached
        if ((! $this->getBody()->isReadable() || ! $this->getBody()->isSeekable())
            && ($this->getContentLength() > 0 || $this->getTransferEncoding() == 'chunked')) {
            return false;
        }
        // Never cache no-store resources (this is a private cache, so private can be cached)
        // TODO : vérifier si dans le cas ou la directive est "private" on doit cached ou pas la réponse ?????
        if ($this->getHeader('Cache-Control') && $this->getHeader('Cache-Control')->hasDirective('no-store')) {
            return false;
        }

        return $this->isFresh() || $this->getFreshness() === null || $this->canValidate();
    }

    /**
     * Gets the number of seconds from the current time in which this response is still considered fresh.
     *
     * @return int|null Returns the number of seconds
     */
    public function getMaxAge()
    {
        if ($header = $this->getHeader('Cache-Control')) {
            // s-max-age, then max-age, then Expires
            if ($age = $header->getDirective('s-maxage')) {
                return $age;
            }
            if ($age = $header->getDirective('max-age')) {
                return $age;
            }
        }
        if ($this->getHeader('Expires')) {
            return strtotime($this->getExpires()) - time();
            // TODO : regarder ici on dirait qu'on soustrait le champ Expire - Date, et non pas comme dans l'exemple au dessus ou on soutrait Expire - time()
            // https://github.com/symfony/symfony/blob/master/src/Symfony/Component/HttpFoundation/Response.php#L751
        }
    }

    /**
     * Check if the response is considered fresh.
     *
     * A response is considered fresh when its age is less than or equal to the freshness lifetime (maximum age) of the
     * response.
     *
     * @return bool|null
     */
    // TODO : éviter de pouvoir renvoyer null il faudrait ajouter ":bool" à la méthode et retourner true ou false uniquement
    public function isFresh()
    {
        $fresh = $this->getFreshness();

        return $fresh === null ? null : $fresh >= 0;
    }

    /**
     * Check if the response can be validated against the origin server using a conditional GET request.
     *
     * @return bool
     */
    // TODO : renommer en isValidateable() ????
    public function canValidate()
    {
        return $this->getEtag() || $this->getLastModified();
    }

    /**
     * Get the freshness of the response by returning the difference of the maximum lifetime of the response and the
     * age of the response (max-age - age).
     *
     * Freshness values less than 0 mean that the response is no longer fresh and is ABS(freshness) seconds expired.
     * Freshness values of greater than zero is the number of seconds until the response is no longer fresh. A NULL
     * result means that no freshness information is available.
     *
     * @return int
     */
    public function getFreshness()
    {
        $maxAge = $this->getMaxAge();
        $age = $this->calculateAge();

        return $maxAge && $age ? ($maxAge - $age) : null;
    }

    /**
     * Calculate the age of the response.
     *
     * @return int
     */
    public function calculateAge()
    {
        $age = $this->getHeader('Age');
        if ($age === null && $this->getDate()) {
            $age = time() - strtotime($this->getDate());
        }

        return $age === null ? null : (int) (string) $age;
    }

    /*******************************************************************************
     * Cache
     // TODO : regarder ici comment c'est fait : https://github.com/micheh/psr7-cache/blob/master/src/CacheUtil.php    ou ici : https://github.com/slimphp/Slim-HttpCache/blob/master/src/CacheProvider.php
     ******************************************************************************/

    /**
     * Enable client-side HTTP caching
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param ResponseInterface $response       PSR7 response object
     * @param string            $type           Cache-Control type: "private" or "public"
     * @param null|int|string   $maxAge         Maximum cache age (integer timestamp or datetime string)
     * @param bool              $mustRevalidate add option "must-revalidate" to Cache-Control
     *
     * @throws InvalidArgumentException if the cache-control type is invalid
     *
     * @return ResponseInterface A new PSR7 response object with `Cache-Control` header
     */
    public function allowCache(ResponseInterface $response, $type = 'private', $maxAge = null, $mustRevalidate = false)
    {
        if (! in_array($type, ['private', 'public'])) {
            throw new InvalidArgumentException('Invalid Cache-Control type. Must be "public" or "private".');
        }
        $headerValue = $type;
        if ($maxAge || is_int($maxAge)) {
            if (! is_int($maxAge)) {
                $maxAge = strtotime($maxAge);
            }
            // TODO : il faudrait peut etre utiliser un "no-cache" au lieu de max-age=0 dans le cas ou la variable $maxAge === 0    : regarder ici : https://github.com/slimphp/Slim-HttpCache/blob/master/src/Cache.php#L59
            $headerValue = $headerValue . ', max-age=' . $maxAge;
        }
        if ($mustRevalidate) {
            $headerValue = $headerValue . ', must-revalidate';
        }

        return $response->withHeader('Cache-Control', $headerValue);
    }

    /**
     * Disable client-side HTTP caching
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param ResponseInterface $response PSR7 response object
     *
     * @return ResponseInterface A new PSR7 response object with `Cache-Control` header
     */
    public function denyCache(ResponseInterface $response)
    {
        return $response->withHeader('Cache-Control', 'no-store,no-cache');
    }

    /**
     * Add `Expires` header to PSR7 response object
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param ResponseInterface $response A PSR7 response object
     * @param int|string        $time     A UNIX timestamp or a valid `strtotime()` string
     *
     * @throws InvalidArgumentException if the expiration date cannot be parsed
     *
     * @return ResponseInterface A new PSR7 response object with `Expires` header
     */
    // TODO : regarder aussi ici : https://github.com/micheh/psr7-cache/blob/master/src/CacheUtil.php#L86
    public function withExpires(ResponseInterface $response, $time)
    {
        if (! is_int($time)) {
            $time = strtotime($time);
            if ($time === false) {
                throw new InvalidArgumentException('Expiration value could not be parsed with `strtotime()`.');
            }
        }

        return $response->withHeader('Expires', gmdate('D, d M Y H:i:s T', $time));
    }

    /**
     * Add `ETag` header to PSR7 response object
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param ResponseInterface $response A PSR7 response object
     * @param string            $value    The ETag value
     * @param string            $type     ETag type: "strong" or "weak"
     *
     * @throws InvalidArgumentException if the etag type is invalid
     *
     * @return ResponseInterface A new PSR7 response object with `ETag` header
     */
    // TODO : regarder aussi ici : https://github.com/micheh/psr7-cache/blob/master/src/CacheUtil.php#L132
    public function withEtag(ResponseInterface $response, $value, $type = 'strong')
    {
        if (! in_array($type, ['strong', 'weak'])) {
            throw new InvalidArgumentException('Invalid etag type. Must be "strong" or "weak".');
        }
        $value = '"' . $value . '"';
        if ($type === 'weak') {
            $value = 'W/' . $value;
        }

        return $response->withHeader('ETag', $value);
    }

    /**
     * Add `Last-Modified` header to PSR7 response object
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param ResponseInterface $response A PSR7 response object
     * @param int|string        $time     A UNIX timestamp or a valid `strtotime()` string
     *
     * @throws InvalidArgumentException if the last modified date cannot be parsed
     *
     * @return ResponseInterface A new PSR7 response object with `Last-Modified` header
     */
    public function withLastModified(ResponseInterface $response, $time)
    {
        if (! is_int($time)) {
            $time = strtotime($time);
            if ($time === false) {
                throw new InvalidArgumentException('Last Modified value could not be parsed with `strtotime()`.');
            }
        }

        return $response->withHeader('Last-Modified', gmdate('D, d M Y H:i:s T', $time));
    }

    /**
     * Modifies the response so that it conforms to the rules defined for a 304 status code.
     *
     * This sets the status, removes the body, and discards any headers
     * that MUST NOT be included in 304 responses.
     *
     * @return $this
     *
     * @see http://tools.ietf.org/html/rfc2616#section-10.3.5
     *
     * @final since version 3.3
     */
    //https://github.com/symfony/symfony/blob/master/src/Symfony/Component/HttpFoundation/Response.php#L981
    // TODO : à mettre dans une classe CacheUtil
    public function setNotModified()
    {
        $this->setStatusCode(304);
        $this->setContent(null);
        // remove headers that MUST NOT be included with 304 Not Modified responses
        foreach (['Allow', 'Content-Encoding', 'Content-Language', 'Content-Length', 'Content-MD5', 'Content-Type', 'Last-Modified'] as $header) {
            $this->headers->remove($header);
        }

        return $this;
    }

    /**
     * Determines if the Response validators (ETag, Last-Modified) match
     * a conditional value specified in the Request.
     *
     * If the Response is not modified, it sets the status code to 304 and
     * removes the actual content by calling the setNotModified() method.
     *
     * @return bool true if the Response validators match the Request, false otherwise
     *
     * @final since version 3.3
     */
    // TODO : à mettre dans un Middleware plutot
    // TODO : à mettre dans une classe CacheUtil car on passe une request en paramétre alors qu'ici on est dans la classe Response() !!!!
    // TODO : regarder ici un autre exemple : https://github.com/micheh/psr7-cache/blob/master/src/CacheUtil.php#L260
    //https://github.com/symfony/symfony/blob/master/src/Symfony/Component/HttpFoundation/Response.php#L1051
    public function isNotModified(Request $request): bool
    {
        if (! $request->isMethodCacheable()) {
            return false;
        }
        $notModified = false;
        $lastModified = $this->headers->get('Last-Modified');
        $modifiedSince = $request->headers->get('If-Modified-Since');
        if ($etags = $request->getETags()) {
            $notModified = in_array($this->getEtag(), $etags) || in_array('*', $etags);
        }
        if ($modifiedSince && $lastModified) {
            $notModified = strtotime($modifiedSince) >= strtotime($lastModified) && (! $etags || $notModified);
        }
        if ($notModified) {
            $this->setNotModified();
        }

        return $notModified;
    }

    /**
     * Convert response to string.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string
     */
    // TODO : regarder ici : https://github.com/kiler129/CherryHttp/blob/05927f26183cbd6fd5ccb0853befecdea279308d/src/Http/Response/Response.php#L122
    //https://github.com/slimphp/Slim-Http/blob/master/src/Response.php#L462
    public function __toString()
    {
        $output = sprintf(
            'HTTP/%s %s %s',
            $this->getProtocolVersion(),
            $this->getStatusCode(),
            $this->getReasonPhrase()
        );
        $output .= self::EOL;
        $output .= 'HEADERS :' . self::EOL;
        foreach ($this->getHeaders() as $name => $values) {
            $output .= sprintf('    %s: %s', $name, $this->getHeaderLine($name)) . self::EOL;
        }
        $output .= 'BODY :' . self::EOL;
        $output .= (string) $this->getBody();

        return $output;
    }

    /**
     * Sets the Date header.
     *
     * @return $this
     *
     * @final
     */
    //https://github.com/symfony/http-foundation/blob/master/Response.php#L649
    public function setDate(\DateTimeInterface $date): self
    {
        if ($date instanceof \DateTime) {
            $date = \DateTimeImmutable::createFromMutable($date);
        }
        $date = $date->setTimezone(new \DateTimeZone('UTC'));
        $this->headers->set('Date', $date->format('D, d M Y H:i:s') . ' GMT');

        return $this;
    }

    /**
     * Sets the ETag value.
     *
     * @param string|null $etag The ETag unique identifier or null to remove the header
     * @param bool        $weak Whether you want a weak ETag or not
     *
     * @return $this
     *
     * @final
     */
    public function setEtag(string $etag = null, bool $weak = false): self
    {
        if (null === $etag) {
            $this->headers->remove('Etag');
        } else {
            if (0 !== strpos($etag, '"')) {
                $etag = '"' . $etag . '"';
            }
            $this->headers->set('ETag', (true === $weak ? 'W/' : '') . $etag);
        }

        return $this;
    }

    /**
     * Sets the response's cache headers (validation and/or expiration).
     *
     * Available options are: etag, last_modified, max_age, s_maxage, private, public and immutable.
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     *
     *
     * @final
     */
    public function setCache(array $options) : self
    {
        if ($diff = array_diff(array_keys($options), ['etag', 'last_modified', 'max_age', 's_maxage', 'private', 'public', 'immutable'])) {
            throw new \InvalidArgumentException(sprintf('Response does not support the following options: "%s".', implode('", "', array_values($diff))));
        }
        if (isset($options['etag'])) {
            $this->setEtag($options['etag']);
        }
        if (isset($options['last_modified'])) {
            $this->setLastModified($options['last_modified']);
        }
        if (isset($options['max_age'])) {
            $this->setMaxAge($options['max_age']);
        }
        if (isset($options['s_maxage'])) {
            $this->setSharedMaxAge($options['s_maxage']);
        }
        if (isset($options['public'])) {
            if ($options['public']) {
                $this->setPublic();
            } else {
                $this->setPrivate();
            }
        }
        if (isset($options['private'])) {
            if ($options['private']) {
                $this->setPrivate();
            } else {
                $this->setPublic();
            }
        }
        if (isset($options['immutable'])) {
            $this->setImmutable((bool) $options['immutable']);
        }

        return $this;
    }

    /**
     * Returns an array of header names given in the Vary header.
     *
     * @final
     */
    /*
    public function getVary(): array
    {
        if (!$vary = $this->headers->get('Vary', null, false)) {
            return array();
        }
        $ret = array();
        foreach ($vary as $item) {
            $ret = array_merge($ret, preg_split('/[\s,]+/', $item));
        }
        return $ret;
    }*/

    /**
     * Sets the Vary header.
     *
     * @param string|array $headers
     * @param bool         $replace Whether to replace the actual value or not (true by default)
     *
     * @return $this
     *
     * @final
     */
    public function setVary($headers, bool $replace = true)
    {
        $this->headers->set('Vary', $headers, $replace);

        return $this;
    }







    /**
     * Get the Accept-Ranges HTTP header.
     *
     * @return string Returns what partial content range types this server supports.
     */
    public function getAcceptRanges(): string
    {
        return $this->getHeaderLine('Accept-Ranges');
    }

    /**
     * Get the Age HTTP header.
     *
     * @return int|null Returns the age the object has been in a proxy cache in seconds, or null if header not present.
     */
    public function getAge(): ?int
    {
        return $this->hasHeader('Age') ? (int) $this->getHeaderLine('Age') : null;
    }

    /**
     * Get the Allow HTTP header.
     *
     * @return string[]| null Returns valid actions for a specified resource, or empty array. To be used for a 405 Method not allowed.
     */
    public function getAllow(): ?array
    {
        return $this->hasHeader('Allow') ? array_map('trim', explode(',', $this->getHeaderLine('Allow'))) : null;
    }

    /**
     * Check if an HTTP method is allowed by checking the Allow response header.
     *
     * @param string $method Method to check
     *
     * @return bool
     */
    public function isMethodAllowed(string $method): bool
    {
        $methods = $this->getAllow();
        if ($methods) {
            return in_array(strtoupper($method), $methods);
        }

        return false;
    }

    /**
     * Get the Cache-Control HTTP header.
     *
     * @return string
     */
    public function getCacheControl()
    {
        return $this->getHeaderLine('Cache-Control');
    }

    /**
     * Get the Connection HTTP header.
     *
     * @return string
     */
    public function getConnection()
    {
        return $this->getHeaderLine('Connection');
    }

    /**
     * Get the Content-Encoding HTTP header.
     *
     * @return string|null
     */
    public function getContentEncoding()
    {
        return $this->getHeaderLine('Content-Encoding');
    }

    /**
     * Get the Content-Language HTTP header.
     *
     * @return string|null Returns the language the content is in.
     */
    public function getContentLanguage()
    {
        return $this->getHeaderLine('Content-Language');
    }

    /**
     * Get the Content-Length HTTP header.
     *
     * @return int Returns the length of the response body in bytes
     */
    public function getContentLength()
    {
        return (int) $this->getHeaderLine('Content-Length');
    }

    /**
     * Get the Content-Location HTTP header.
     *
     * @return string|null Returns an alternate location for the returned data (e.g /index.htm)
     */
    public function getContentLocation()
    {
        return $this->getHeaderLine('Content-Location');
    }

    /**
     * Get the Content-Disposition HTTP header.
     *
     * @return string|null Returns the Content-Disposition header
     */
    public function getContentDisposition()
    {
        return $this->getHeaderLine('Content-Disposition');
    }

    /**
     * Get the Content-MD5 HTTP header.
     *
     * @return string|null Returns a Base64-encoded binary MD5 sum of the content of the response.
     */
    public function getContentMd5()
    {
        return $this->getHeaderLine('Content-MD5');
    }

    /**
     * Get the Content-Range HTTP header.
     *
     * @return string Returns where in a full body message this partial message belongs (e.g. bytes 21010-47021/47022).
     */
    public function getContentRange()
    {
        return $this->getHeaderLine('Content-Range');
    }

    /**
     * Get the Content-Type HTTP header.
     *
     * @return string Returns the mime type of this content.
     */
    public function getContentType()
    {
        return $this->getHeaderLine('Content-Type');
    }

    /**
     * Checks if the Content-Type is of a certain type.  This is useful if the
     * Content-Type header contains charset information and you need to know if
     * the Content-Type matches a particular type.
     *
     * @param string $type Content type to check against
     *
     * @return bool
     */
    public function isContentType($type)
    {
        return stripos($this->getHeaderLine('Content-Type'), $type) !== false;
    }

    /**
     * Get the Date HTTP header.
     *
     * @return string|null Returns the date and time that the message was sent.
     */
    public function getDate()
    {
        return $this->getHeaderLine('Date');
    }

    /**
     * Get the ETag HTTP header.
     *
     * @return string|null Returns an identifier for a specific version of a resource, often a Message digest.
     */
    public function getEtag()
    {
        return $this->getHeaderLine('ETag');
    }

    /**
     * Get the Expires HTTP header.
     *
     * @return string|null Returns the date/time after which the response is considered stale.
     */
    public function getExpires()
    {
        return $this->getHeaderLine('Expires');
    }

    /**
     * Get the Last-Modified HTTP header.
     *
     * @return string|null Returns the last modified date for the requested object, in RFC 2822 format
     *                     (e.g. Tue, 15 Nov 1994 12:45:26 GMT)
     */
    public function getLastModified()
    {
        return $this->getHeaderLine('Last-Modified');
    }

    /**
     * Get the Location HTTP header.
     *
     * @return string|null Used in redirection, or when a new resource has been created.
     */
    public function getLocation()
    {
        return $this->getHeaderLine('Location');
    }

    /**
     * Get the Pragma HTTP header.
     *
     * @return Header|null Returns the implementation-specific headers that may have various effects anywhere along
     *                     the request-response chain.
     */
    public function getPragma()
    {
        return $this->getHeaderLine('Pragma');
    }

    /**
     * Get the Proxy-Authenticate HTTP header.
     *
     * @return string|null Authentication to access the proxy (e.g. Basic)
     */
    public function getProxyAuthenticate()
    {
        return $this->getHeaderLine('Proxy-Authenticate');
    }

    /**
     * Get the Retry-After HTTP header.
     *
     * @return int|null If an entity is temporarily unavailable, this instructs the client to try again after a
     *                  specified period of time.
     */
    public function getRetryAfter()
    {
        return (int) $this->getHeaderLine('Retry-After');
    }

    /**
     * Get the Server HTTP header.
     *
     * @return string|null A name for the server
     */
    public function getServer()
    {
        return $this->getHeaderLine('Server');
    }

    /**
     * Get the Set-Cookie HTTP header.
     *
     * @return string|null An HTTP cookie.
     */
    public function getSetCookie()
    {
        return $this->getHeaderLine('Set-Cookie');
    }

    /**
     * Get the Trailer HTTP header.
     *
     * @return string|null The Trailer general field value indicates that the given set of header fields is present in
     *                     the trailer of a message encoded with chunked transfer-coding.
     */
    public function getTrailer()
    {
        return $this->getHeaderLine('Trailer');
    }

    /**
     * Get the Transfer-Encoding HTTP header.
     *
     * @return string|null The form of encoding used to safely transfer the entity to the user
     */
    public function getTransferEncoding()
    {
        return $this->getHeaderLine('Transfer-Encoding');
    }

    /**
     * Get the Vary HTTP header.
     *
     * @return string|null Tells downstream proxies how to match future request headers to decide whether the cached
     *                     response can be used rather than requesting a fresh one from the origin server.
     */
    // TODO : regarder ici comment c'est fait : https://github.com/symfony/http-foundation/blob/master/Response.php#L1009
    public function getVary()
    {
        return $this->getHeaderLine('Vary');
    }

    /**
     * Get the Via HTTP header.
     *
     * @return string|null Informs the client of proxies through which the response was sent.
     */
    public function getVia()
    {
        return $this->getHeaderLine('Via');
    }

    /**
     * Get the Warning HTTP header.
     *
     * @return string|null A general warning about possible problems with the entity body
     */
    public function getWarning()
    {
        return $this->getHeaderLine('Warning');
    }

    /**
     * Get the WWW-Authenticate HTTP header.
     *
     * @return string|null Indicates the authentication scheme that should be used to access the requested entity
     */
    public function getWwwAuthenticate()
    {
        return $this->getHeaderLine('WWW-Authenticate');
    }

    //*************************
    // https://github.com/yiisoft/yii2-httpclient/blob/master/src/Response.php#L84
    //*******************************

    /**
     * Returns default format automatically detected from headers and content.
     *
     * @return string|null format name, 'null' - if detection failed.
     */
    public function detectFormat()
    {
        $format = $this->detectFormatByHeader();
        if ($format === null) {
            $format = $this->detectFormatByContent((string) $this->getBody());
        }

        return $format;
    }

    /**
     * Detects format from headers.
     *
     * @return null|string format name, 'null' - if detection failed.
     */
    private function detectFormatByHeader()
    {
        $contentTypeHeaders = $this->getHeader('Content-Type');
        if (! empty($contentTypeHeaders)) {
            $contentType = end($contentTypeHeaders);
            if (stripos($contentType, 'json') !== false) {
                return self::FORMAT_JSON;
            }
            if (stripos($contentType, 'urlencoded') !== false) {
                return self::FORMAT_URLENCODED;
            }
            if (stripos($contentType, 'xml') !== false) {
                return self::FORMAT_XML;
            }
        }
    }

    /**
     * Detects response format from raw content.
     *
     * @param string $content raw response content.
     *
     * @return null|string format name, 'null' - if detection failed.
     */
    // TODO : on peut surement faire un middleware pour ajouter un contentType = application/json ou /xml ou html/text selon la détection du format ???? cela semble une bonne idée !!!!
    private function detectFormatByContent($content)
    {
        if (preg_match('/^\\{.*\\}$/is', $content)) {
            return self::FORMAT_JSON;
        }
        if (preg_match('/^([^=&])+=[^=&]+(&[^=&]+=[^=&]+)*$/', $content)) {
            return self::FORMAT_URLENCODED;
        }
        if (preg_match('/^<.*>$/s', $content)) {
            return self::FORMAT_XML;
        }
    }

    public function withoutBody()
    {
        return $this->withBody(Body::createFromStringOrResource('php://temp', 'rw+'));
    }
}
