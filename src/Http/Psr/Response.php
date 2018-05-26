<?php

declare(strict_types=1);

namespace Chiron\Http\Psr;

use Chiron\Http\Factory\StreamFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @author Michael Dowling and contributors to guzzlehttp/psr7
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class Response implements ResponseInterface
{
    use MessageTrait;

    /** @var array Map of standard HTTP status code/reason phrases */
    private static $phrases = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];

    /** @var string */
    private $reasonPhrase = '';

    /** @var int */
    private $statusCode = 200;

    /**
     * @param int                                  $status  Status code
     * @param array                                $headers Response headers
     * @param string|null|resource|StreamInterface $body    Response body
     * @param string                               $version Protocol version
     * @param string|null                          $reason  Reason phrase (when empty a default will be used based on the status code)
     */
    public function __construct(
        int $status = 200,
        array $headers = [],
        $body = null,
        string $version = '1.1',
        $reason = null
    ) {
        $this->statusCode = (int) $status;

        if ('' !== $body && null !== $body) {
            $this->stream = (new StreamFactory())->createStream($body);
        }

        $this->setHeaders($headers);
        if (null === $reason && isset(self::$phrases[$this->statusCode])) {
            $this->reasonPhrase = self::$phrases[$status];
        } else {
            $this->reasonPhrase = (string) $reason;
        }

        $this->protocol = $version;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    public function withStatus($code, $reasonPhrase = ''): self
    {
        if (! is_int($code) && ! is_string($code)) {
            throw new \InvalidArgumentException('Status code has to be an integer');
        }

        $code = (int) $code;
        if ($code < 100 || $code > 599) {
            throw new \InvalidArgumentException('Status code has to be an integer between 100 and 599');
        }

        $new = clone $this;
        $new->statusCode = (int) $code;
        if ('' == $reasonPhrase && isset(self::$phrases[$new->statusCode])) {
            $reasonPhrase = self::$phrases[$new->statusCode];
        }
        $new->reasonPhrase = $reasonPhrase;

        return $new;
    }

    public const FORMAT_URLENCODED = 'URLENCODED';

    public const FORMAT_JSON = 'JSON';

    public const FORMAT_XML = 'XML';

    // TODO : il faudrait pas implémenter une méthode clone avec les objets genre header ou cookies ????     https://github.com/slimphp/Slim/blob/3.x/Slim/Http/Response.php#L147
    // TODO : les cookies ne semble pas avoir leur place ici !!!!!!!!!!
    private $cookies = [];

    // https://github.com/guzzle/guzzle3/blob/master/src/Guzzle/Http/Message/Response.php#L99

    /** @var array Cacheable response codes (see RFC 2616:13.4) */
    protected static $cacheResponseCodes = [200, 203, 206, 300, 301, 410];

    // 200, 203, 300, 301, 302, 404, 410
    // TODO : regarder ici la liste : https://github.com/micheh/psr7-cache/blob/master/src/CacheUtil.php#L289

    // TODO : vérifier si on garde l'initialisation du ProtocolVersion en trant que paramétre du constructeur
    // TODO : virer la partie "reason" du constructeur ?????
    //@param string|resource|StreamInterface $body Stream identifier and/or actual stream resource

    //public function __construct($status = 200, $body = 'php://temp', $reason = '', $version = '1.1', array $headers = [])
    /*
    public function __construct(int $status = 200, array $headers = [], $body = null, string $version = '1.1', $reason = null)
    {
        parent::__construct($status, $headers, $body, $version, $reason);
    }*/

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
     * @throws InvalidArgumentException on an invalid status code
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
     * @param string|null $charset character set string
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
     * @param string $charset character set string
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
     * @param \Exception|\Error $e the exception object
     *
     * @throws InvalidArgumentException if the status code is invalid
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
     * @param string|UriInterface $url    the redirect destination
     * @param int|null            $status the redirect HTTP status code
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
            // TODO : on devrait pas vérifier si le code est dans l'interval 3xx ?????
            $responseWithRedirect = $responseWithRedirect->withStatus($status);
        }

        // a message is better when doing a redirection.
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
     * @param int   $status          the HTTP status code
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
    //TODO : à renommer en "writeJson()" ????? ou plutot en withJsonBody
    public function withJson($data, $status = null, $encodingOptions = 79)
    {
        // default encodingOptions is 79 => JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES

        //$response = $this->withBody(new Body('php://temp', 'r+'));
        //$response->stream->write($json = json_encode($data, $encodingOptions));

        $body = StreamFactory::createFromStringOrResource('php://temp', 'r+');
        $body->write($json = json_encode($data, $encodingOptions));

        $response = $this->withBody($body);

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
        return $content instanceof ArrayObject ||
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
    // TODO : méthode à virer ou alors à minima lui passer en paramétre les options pour l'encodage en JSON
    protected function morphToJson($content)
    {
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

    //*****************************************************************************
    //****************************** cakePHP Response *****************************
    //*****************************************************************************

    /**
     * Convenience method to set a string into the response body.
     *
     * @param string $string The string to be sent
     *
     * @return static
     */
    public function withStringBody($string)
    {
        $new = clone $this;
        $new->_createStream();
        $new->stream->write((string) $string);

        return $new;
    }

    /**
     * Stream target or resource object.
     *
     * @var string|resource
     */
    protected $_streamTarget = 'php://memory';

    /**
     * Stream mode options.
     *
     * @var string
     */
    protected $_streamMode = 'wb+';

    /**
     * Creates the stream object.
     */
    protected function _createStream()
    {
        $this->stream = new Stream($this->_streamTarget, $this->_streamMode);
    }

    /**
     * Create a new response with the Content-Length header set.
     *
     * @param int|string $bytes Number of bytes
     *
     * @return static
     */
    public function withLength($bytes)
    {
        return $this->withHeader('Content-Length', (string) $bytes);
    }

    /**
     * Create a new instance with the Etag header set.
     *
     * Etags are a strong indicative that a response can be cached by a
     * HTTP client. A bad way of generating Etags is creating a hash of
     * the response output, instead generate a unique hash of the
     * unique components that identifies a request, such as a
     * modification time, a resource Id, and anything else you consider it
     * that makes the response unique.
     *
     * The second parameter is used to inform clients that the content has
     * changed, but semantically it is equivalent to existing cached values. Consider
     * a page with a hit counter, two different page views are equivalent, but
     * they differ by a few bytes. This permits the Client to decide whether they should
     * use the cached data.
     *
     * @param string $hash The unique hash that identifies this response
     * @param bool   $weak Whether the response is semantically the same as
     *                     other with the same hash or not. Defaults to false
     *
     * @return static
     */
    public function withEtag2($hash, $weak = false)
    {
        $hash = sprintf('%s"%s"', $weak ? 'W/' : null, $hash);

        return $this->withHeader('Etag', $hash);
    }

    /**
     * Create a new instance with the Vary header set.
     *
     * If an array is passed values will be imploded into a comma
     * separated string. If no parameters are passed, then an
     * array with the current Vary header value is returned
     *
     * @param string|array $cacheVariances A single Vary string or an array
     *                                     containing the list for variances.
     *
     * @return static
     */
    public function withVary2($cacheVariances)
    {
        return $this->withHeader('Vary', (array) $cacheVariances);
    }

    /**
     * Create a new instance as 'not modified'.
     *
     * This will remove any body contents set the status code
     * to "304" and removing headers that describe
     * a response body.
     *
     * @return static
     */
    public function withNotModified2()
    {
        $new = $this->withStatus(304);
        $new->_createStream();
        $remove = [
            'Allow',
            'Content-Encoding',
            'Content-Language',
            'Content-Length',
            'Content-MD5',
            'Content-Type',
            'Last-Modified',
        ];
        foreach ($remove as $header) {
            $new = $new->withoutHeader($header);
        }

        return $new;
    }

    /**
     * Create a new response with a cookie set.
     *
     * ### Options
     *
     * - `value`: Value of the cookie
     * - `expire`: Time the cookie expires in
     * - `path`: Path the cookie applies to
     * - `domain`: Domain the cookie is for.
     * - `secure`: Is the cookie https?
     * - `httpOnly`: Is the cookie available in the client?
     *
     * ### Examples
     *
     * ```
     * // set scalar value with defaults
     * $response = $response->withCookie('remember_me', 1);
     *
     * // customize cookie attributes
     * $response = $response->withCookie('remember_me', ['path' => '/login']);
     *
     * // add a cookie object
     * $response = $response->withCookie(new Cookie('remember_me', 1));
     * ```
     *
     * @param string|\Cake\Http\Cookie\Cookie $name The name of the cookie to set, or a cookie object
     * @param array|string                    $data Either a string value, or an array of cookie options.
     *
     * @return static
     */
    public function withCookie2($name, $data = '')
    {
        if ($name instanceof Cookie) {
            $cookie = $name;
        } else {
            if (! is_array($data)) {
                $data = ['value' => $data];
            }
            $data += [
                'value'    => '',
                'expire'   => 0,
                'path'     => '/',
                'domain'   => '',
                'secure'   => false,
                'httpOnly' => false,
            ];
            $expires = $data['expire'] ? new DateTime('@' . $data['expire']) : null;
            $cookie = new Cookie(
                $name,
                $data['value'],
                $expires,
                $data['path'],
                $data['domain'],
                $data['secure'],
                $data['httpOnly']
            );
        }
        $new = clone $this;
        $new->_cookies = $new->_cookies->add($cookie);

        return $new;
    }

    /**
     * Create a new response with an expired cookie set.
     *
     * ### Options
     *
     * - `path`: Path the cookie applies to
     * - `domain`: Domain the cookie is for.
     * - `secure`: Is the cookie https?
     * - `httpOnly`: Is the cookie available in the client?
     *
     * ### Examples
     *
     * ```
     * // set scalar value with defaults
     * $response = $response->withExpiredCookie('remember_me');
     *
     * // customize cookie attributes
     * $response = $response->withExpiredCookie('remember_me', ['path' => '/login']);
     *
     * // add a cookie object
     * $response = $response->withExpiredCookie(new Cookie('remember_me'));
     * ```
     *
     * @param string|\Cake\Http\Cookie\CookieInterface $name    The name of the cookie to expire, or a cookie object
     * @param array                                    $options An array of cookie options.
     *
     * @return static
     */
    public function withExpiredCookie($name, $options = [])
    {
        if ($name instanceof CookieInterface) {
            $cookie = $name->withExpired();
        } else {
            $options += [
                'path'     => '/',
                'domain'   => '',
                'secure'   => false,
                'httpOnly' => false,
            ];
            $cookie = new Cookie(
                $name,
                '',
                DateTime::createFromFormat('U', 1),
                $options['path'],
                $options['domain'],
                $options['secure'],
                $options['httpOnly']
            );
        }
        $new = clone $this;
        $new->_cookies = $new->_cookies->add($cookie);

        return $new;
    }

    /**
     * Read a single cookie from the response.
     *
     * This method provides read access to pending cookies. It will
     * not read the `Set-Cookie` header if set.
     *
     * @param string $name The cookie name you want to read.
     *
     * @return array|null Either the cookie data or null
     */
    public function getCookie($name)
    {
        if (! $this->_cookies->has($name)) {
            return;
        }
        $cookie = $this->_cookies->get($name);

        return $this->convertCookieToArray($cookie);
    }

    /**
     * Get all cookies in the response.
     *
     * Returns an associative array of cookie name => cookie data.
     *
     * @return array
     */
    public function getCookies()
    {
        $out = [];
        foreach ($this->_cookies as $cookie) {
            $out[$cookie->getName()] = $this->convertCookieToArray($cookie);
        }

        return $out;
    }

    /**
     * Convert the cookie into an array of its properties.
     *
     * This method is compatible with the historical behavior of Cake\Http\Response,
     * where `httponly` is `httpOnly` and `expires` is `expire`
     *
     * @param \Cake\Http\Cookie\CookieInterface $cookie Cookie object.
     *
     * @return array
     */
    protected function convertCookieToArray(CookieInterface $cookie)
    {
        return [
            'name'     => $cookie->getName(),
            'value'    => $cookie->getStringValue(),
            'path'     => $cookie->getPath(),
            'domain'   => $cookie->getDomain(),
            'secure'   => $cookie->isSecure(),
            'httpOnly' => $cookie->isHttpOnly(),
            'expire'   => $cookie->getExpiresTimestamp(),
        ];
    }

    /**
     * Get the CookieCollection from the response.
     *
     * @return \Cake\Http\Cookie\CookieCollection
     */
    public function getCookieCollection()
    {
        return $this->_cookies;
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
    // TODO : il faut surement ajouter un must-revalidate : exemple : https://github.com/micheh/psr7-cache/blob/master/src/Header/ResponseCacheControl.php#L199
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
    public function setCache(array $options): self
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
     * @return string returns what partial content range types this server supports
     */
    public function getAcceptRanges(): string
    {
        return $this->getHeaderLine('Accept-Ranges');
    }

    /**
     * Get the Age HTTP header.
     *
     * @return int|null returns the age the object has been in a proxy cache in seconds, or null if header not present
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
     * @return string|null returns the language the content is in
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
     * @return string|null returns a Base64-encoded binary MD5 sum of the content of the response
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
     * @return string returns the mime type of this content
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
     * @return string|null returns the date and time that the message was sent
     */
    public function getDate()
    {
        return $this->getHeaderLine('Date');
    }

    /**
     * Get the ETag HTTP header.
     *
     * @return string|null returns an identifier for a specific version of a resource, often a Message digest
     */
    public function getEtag()
    {
        return $this->getHeaderLine('ETag');
    }

    /**
     * Get the Expires HTTP header.
     *
     * @return string|null returns the date/time after which the response is considered stale
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
     * @return string|null used in redirection, or when a new resource has been created
     */
    public function getLocation()
    {
        return $this->getHeaderLine('Location');
    }

    /**
     * Get the Pragma HTTP header.
     *
     * @return Header|null returns the implementation-specific headers that may have various effects anywhere along
     *                     the request-response chain
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
     * @return int|null if an entity is temporarily unavailable, this instructs the client to try again after a
     *                  specified period of time
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
     * @return string|null an HTTP cookie
     */
    public function getSetCookie()
    {
        return $this->getHeaderLine('Set-Cookie');
    }

    /**
     * Get the Trailer HTTP header.
     *
     * @return string|null the Trailer general field value indicates that the given set of header fields is present in
     *                     the trailer of a message encoded with chunked transfer-coding
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
     * @return string|null tells downstream proxies how to match future request headers to decide whether the cached
     *                     response can be used rather than requesting a fresh one from the origin server
     */
    // TODO : regarder ici comment c'est fait : https://github.com/symfony/http-foundation/blob/master/Response.php#L1009
    public function getVary()
    {
        return $this->getHeaderLine('Vary');
    }

    /**
     * Get the Via HTTP header.
     *
     * @return string|null informs the client of proxies through which the response was sent
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
     * @return string|null format name, 'null' - if detection failed
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
     * @return null|string format name, 'null' - if detection failed
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
     * @param string $content raw response content
     *
     * @return null|string format name, 'null' - if detection failed
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
        return $this->withBody(StreamFactory::createFromStringOrResource('php://temp', 'rw+'));
    }

    //*******************************************************************
    //https://github.com/cakephp/cakephp/blob/master/src/Http/Response.php#L1300
    //*******************************************************************

    /**
     * Create a new instance with headers to instruct the client to not cache the response.
     *
     * @return static
     */
    public function withDisabledCache()
    {
        return $this->withHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT')
            ->withHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT')
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    }

    /**
     * Create a new instance with the Last-Modified header set.
     *
     * ### Examples:
     *
     * ```
     * // Will Expire the response cache now
     * $response->withModified('now')
     *
     * // Will set the expiration in next 24 hours
     * $response->withModified(new DateTime('+1 day'))
     * ```
     *
     * @param string|\DateTime $time Valid time string or \DateTime instance.
     *
     * @return static
     */
    public function withModified($time)
    {
        $date = $this->_getUTCDate($time);

        return $this->withHeader('Last-Modified', $date->format('D, j M Y H:i:s') . ' GMT');
    }

    /**
     * Returns a DateTime object initialized at the $time param and using UTC
     * as timezone.
     *
     * @param string|int|\DateTime|null $time Valid time string or \DateTime instance.
     *
     * @return \DateTime
     */
    //https://github.com/cakephp/cakephp/blob/master/src/Http/Response.php#L1860
    //https://github.com/micheh/psr7-cache/blob/master/src/CacheUtil.php#L423
    protected function _getUTCDate($time = null)
    {
        if ($time instanceof DateTime) {
            $result = clone $time;
        } elseif (is_int($time)) {
            $result = new DateTime(date('Y-m-d H:i:s', $time));
        } else {
            $result = new DateTime($time);
        }
        $result->setTimezone(new DateTimeZone('UTC'));

        return $result;
    }

    //https://github.com/micheh/psr7-cache/blob/master/src/CacheUtil.php#L390

    /**
     * Returns a formatted timestamp of the time parameter, to use in the HTTP headers. The time
     * parameter can be an UNIX timestamp, a parseable string or a DateTime object.
     *
     * @see https://secure.php.net/manual/en/datetime.formats.php
     *
     * @param int|string|DateTime $time Timestamp, date string or DateTime object
     *
     * @throws InvalidArgumentException If the time could not be parsed
     *
     * @return string Formatted timestamp
     */
    protected function getTimeFromValue($time)
    {
        $format = 'D, d M Y H:i:s \G\M\T';
        if (is_int($time)) {
            return gmdate($format, $time);
        }
        if (is_string($time)) {
            try {
                $time = new DateTime($time);
            } catch (Exception $exception) {
                // if it is an invalid date string an exception is thrown below
            }
        }
        if ($time instanceof DateTime) {
            $time = clone $time;
            $time->setTimezone(new DateTimeZone('UTC'));

            return $time->format($format);
        }

        throw new InvalidArgumentException('Could not create a valid date from ' . gettype($time) . '.');
    }

    /**
     * Returns the Unix timestamp of the time parameter. The parameter can be an Unix timestamp,
     * string or a DateTime object.
     *
     * @param int|string|DateTime $time
     *
     * @throws InvalidArgumentException If the time could not be parsed
     *
     * @return int Unix timestamp
     */
    protected function getTimestampFromValue($time)
    {
        if (is_int($time)) {
            return $time;
        }
        if ($time instanceof DateTime) {
            return $time->getTimestamp();
        }
        if (is_string($time)) {
            return strtotime($time);
        }

        throw new InvalidArgumentException('Could not create timestamp from ' . gettype($time) . '.');
    }
}
