<?php

declare(strict_types=1);

namespace Chiron\Http\Psr;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

/**
 * @author Michael Dowling and contributors to guzzlehttp/psr7
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ServerRequest extends Request implements ServerRequestInterface
{
    /** @var array */
    private $attributes = [];

    /** @var array */
    private $cookieParams = [];

    /** @var null|array|object */
    private $parsedBody;

    /** @var array */
    private $queryParams = [];

    /** @var array */
    private $serverParams;

    /** @var UploadedFileInterface[] */
    private $uploadedFiles = [];

    /**
     * @param string                               $method       HTTP method
     * @param string|UriInterface                  $uri          URI
     * @param array                                $headers      Request headers
     * @param string|null|resource|StreamInterface $body         Request body
     * @param string                               $version      Protocol version
     * @param array                                $serverParams Typically the $_SERVER superglobal
     */
    public function __construct(
        $method,
        $uri,
        array $headers = [],
        $body = null,
        $version = '1.1',
        array $serverParams = []
    ) {
        $this->serverParams = $serverParams;

        parent::__construct($method, $uri, $headers, $body, $version);
    }

    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    public function withUploadedFiles(array $uploadedFiles)
    {
        $new = clone $this;
        $new->uploadedFiles = $uploadedFiles;

        return $new;
    }

    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    public function withCookieParams(array $cookies)
    {
        $new = clone $this;
        $new->cookieParams = $cookies;

        return $new;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function withQueryParams(array $query)
    {
        $new = clone $this;
        $new->queryParams = $query;

        return $new;
    }

    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    public function withParsedBody($data)
    {
        if (! is_array($data) && ! is_object($data) && null !== $data) {
            throw new \InvalidArgumentException('First parameter to withParsedBody MUST be object, array or null');
        }

        $new = clone $this;
        $new->parsedBody = $data;

        return $new;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute($attribute, $default = null)
    {
        if (false === array_key_exists($attribute, $this->attributes)) {
            return $default;
        }

        return $this->attributes[$attribute];
    }

    public function withAttribute($attribute, $value): self
    {
        $new = clone $this;
        $new->attributes[$attribute] = $value;

        return $new;
    }

    public function withoutAttribute($attribute): self
    {
        if (false === array_key_exists($attribute, $this->attributes)) {
            return $this;
        }

        $new = clone $this;
        unset($new->attributes[$attribute]);

        return $new;
    }

    private $contentTypes;

    private $languages;

    /**
     * @param string                               $method       HTTP method
     * @param string|UriInterface                  $uri          URI
     * @param array                                $headers      Request headers
     * @param string|null|resource|StreamInterface $body         Request body
     * @param string                               $version      Protocol version
     * @param array                                $serverParams Typically the $_SERVER superglobal
     */
    /*
    public function __construct(
        $method,
        $uri,
        array $headers = [],
        $body = null,
        $version = '1.1',
        array $serverParams = []
    ) {
        $this->serverParams = $serverParams;

        parent::__construct($method, $uri, $headers, $body, $version, $serverParams);
    }*/

    /**
     * Check if the cookie exist in the request.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasCookie(string $name): bool
    {
        return array_key_exists($name, $this->getCookieParams());
    }

    /**
     * Create a new instance with the specified derived request attributes.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * This method allows setting all new derived request attributes as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * updated attributes.
     *
     * @param array $attributes New attributes
     *
     * @return static
     */
    // TODO : il faudrait pas plutot faire un array_merge ? ou un array_replace ? pour éviter de perdre les attributs existants
    // TODO : créer une méthode withoutAttributes pour vider tous les attributs. non ? ou directement un setAttributes($array, $merge = false) ?
    public function withAttributes(array $attributes)
    {
        $clone = clone $this;
        $clone->attributes = $attributes;

        return $clone;
    }

    /**
     * Does this request use a given method?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param string $method HTTP method
     *
     * @return bool
     */
    public function isMethod(string $method): bool
    {
        // TODO : on devrait pas faire un === aprés avoir fait un lowercase sur la méthode ?
        //return $this->getMethod() === $method;
        return strcasecmp($this->getMethod(), $method) === 0;
    }

    /**
     * Is this a GET request?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isGet()
    {
        return $this->isMethod('GET');
    }

    /**
     * Is this a POST request?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isPost()
    {
        return $this->isMethod('POST');
    }

    /**
     * Is this a PUT request?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isPut()
    {
        return $this->isMethod('PUT');
    }

    /**
     * Is this a PATCH request?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isPatch()
    {
        return $this->isMethod('PATCH');
    }

    /**
     * Is this a DELETE request?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isDelete()
    {
        return $this->isMethod('DELETE');
    }

    /**
     * Is this a HEAD request?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isHead()
    {
        return $this->isMethod('HEAD');
    }

    /**
     * Is this a OPTIONS request?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isOptions()
    {
        return $this->isMethod('OPTIONS');
    }

    /**
     * Is this a PURGE request? ('PURGE' is not an official method described in RFC).
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isPurge()
    {
        return $this->isMethod('PURGE');
    }

    /**
     * Returns whether this is an AJAX (XMLHttpRequest) request.
     *
     * Note that jQuery doesn't set the header in case of cross domain
     * requests: https://stackoverflow.com/questions/8163703/cross-domain-ajax-doesnt-send-x-requested-with-header
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool whether this is an AJAX (XMLHttpRequest) request
     */
    public function isAjax()
    {
        return $this->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * Is this an XHR request? it's an alias for isAjax().
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isXhr()
    {
        return $this->isAjax();
    }

    /**
     * Returns whether this is a PJAX request.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool whether this is a PJAX request
     */
    public function IsPjax()
    {
        return $this->IsAjax() && $this->hasHeader('X-Pjax');
    }

    /**
     * Get request content type.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string|null The request content type, if known
     */
    public function getContentType()
    {
        $result = $this->getHeader('Content-Type');

        return $result ? $result[0] : null;
    }

    /**
     * Get request media type, if known.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string|null The request media type, minus content-type params
     */
    public function getMediaType()
    {
        $contentType = $this->getContentType();
        if ($contentType) {
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);

            return strtolower($contentTypeParts[0]);
        }
    }

    /**
     * Get request media type params, if known.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return array
     */
    public function getMediaTypeParams()
    {
        $contentType = $this->getContentType();
        $contentTypeParams = [];
        if ($contentType) {
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);
            $contentTypePartsLength = count($contentTypeParts);
            for ($i = 1; $i < $contentTypePartsLength; $i++) {
                $paramParts = explode('=', $contentTypeParts[$i]);
                $contentTypeParams[strtolower($paramParts[0])] = $paramParts[1];
            }
        }

        return $contentTypeParams;
    }

    /**
     * Get request content character set, if known.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string|null
     */
    public function getContentCharset()
    {
        $mediaTypeParams = $this->getMediaTypeParams();
        if (isset($mediaTypeParams['charset'])) {
            return $mediaTypeParams['charset'];
        }
    }

    /**
     * Get request content length, if known.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return int|null
     */
    public function getContentLength()
    {
        $result = $this->headers->get('Content-Length');

        return $result ? (int) $result[0] : null;
    }

    /**
     * Checks if the request method is of specified type.
     *
     * @param string $method Uppercase request method (GET, POST etc)
     *
     * @return bool
     */
    /*
        public function isMethod($method)
        {
            return $this->getMethod() === strtoupper($method);
        }
    */

    /**
     * Is the request secure?
     *
     * @return bool
     */
    /*
    public function isSecure()
    {
        $https = $this->getServerParam('HTTPS');

        return ! empty($https) && ('off' !== strtolower($https));
    }*/

    public function isSecure()
    {
        return $this->getScheme() === 'https';
    }

    // it's an alias for the function isSecure()
    public function isSsl()
    {
        return $this->isSecure();
    }

    //https://github.com/symfony/symfony/blob/master/src/Symfony/Component/HttpFoundation/Request.php

    /**
     * Gets the request's scheme.
     *
     * @return string
     */
    // TODO : helper à virer, attention à bien modifier la méthode isSecure qui est dépendante de cette méthode !!!!
    public function getScheme()
    {
        return $this->getUri()->getScheme();
    }

    //*******************************************
    // https://github.com/Guzzle3/http/blob/master/Message/Request.php
    //******************************************* START ******************************************

    public function getPath()
    {
        return '/' . ltrim($this->getUri()->getPath(), '/');
    }

    public function getPort()
    {
        return $this->getUri()->getPort();
    }

    /*******************************************************************************
     * Parameters (e.g., POST and GET data)
     ******************************************************************************/

    /**
     * Fetch parameter value from query string.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getQueryParam(string $key, $default = null)
    {
        $getParams = $this->getQueryParams();
        $result = $default;
        if (isset($getParams[$key])) {
            $result = $getParams[$key];
        }

        return $result;
    }

    /**
     * Fetch cookie value from cookies sent by the client to the server.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param string $name    the cookie name
     * @param mixed  $default default value to return if the attribute does not exist
     *
     * @return mixed
     */
    public function getCookieParam(string $name, $default = null)
    {
        if (! $this->hasCookie($name)) {
            return $default;
        }

        return $this->getCookieParams()[$name];
    }

    /**
     * Retrieve a server parameter.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getServerParam(string $key, $default = null)
    {
        $serverParams = $this->getServerParams();

        return isset($serverParams[$key]) ? $serverParams[$key] : $default;
    }

    /**
     * Fetch parameter value from request body.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getParsedBodyParam(string $key, $default = null)
    {
        $postParams = $this->getParsedBody();
        $result = $default;
        if (is_array($postParams) && isset($postParams[$key])) {
            $result = $postParams[$key];
        } elseif (is_object($postParams) && property_exists($postParams, $key)) {
            $result = $postParams->$key;
        }

        return $result;
    }

    // TODO : il faudrait éventuellement créer la méthode : getUploadedFile(string $name, $default = null);

    /**
     * Fetch request parameter value from body or query string (in that order).
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param string $key     the parameter key
     * @param mixed  $default the default value
     *
     * @return mixed the parameter value
     */
    // TODO : méthode pas vraiment utile !!!
    public function getParam(string $key, $default = null)
    {
        $postParams = $this->getParsedBody();
        $getParams = $this->getQueryParams();
        $result = $default;
        if (is_array($postParams) && isset($postParams[$key])) {
            $result = $postParams[$key];
        } elseif (is_object($postParams) && property_exists($postParams, $key)) {
            $result = $postParams->$key;
        } elseif (isset($getParams[$key])) {
            $result = $getParams[$key];
        }

        return $result;
    }

    /**
     * Fetch associative array of body and query string parameters.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param array|null $only list the keys to retrieve
     *
     * @return array|null
     */
    // TODO : méthode pas vraiment utile !!!
    public function getParams(array $only = null)
    {
        $params = $this->getQueryParams();
        $postParams = $this->getParsedBody();
        if ($postParams) {
            $params = array_merge($params, (array) $postParams);
        }
        if ($only) {
            $onlyParams = [];
            foreach ($only as $key) {
                if (array_key_exists($key, $params)) {
                    $onlyParams[$key] = $params[$key];
                }
            }

            return $onlyParams;
        }

        return $params;
    }

    /*

        public function getMethod()
        {
            return $this->getServerVariable('REQUEST_METHOD');
        }
        public function getHttpAccept()
        {
            return $this->getServerVariable('HTTP_ACCEPT');
        }
        public function getReferer()
        {
            return $this->getServerVariable('HTTP_REFERER');
        }
        public function getUserAgent()
        {
            return $this->getServerVariable('HTTP_USER_AGENT');
        }
        public function getIpAddress()
        {
            return $this->getServerVariable('REMOTE_ADDR');
        }
        public function isSecure()
        {
            return (array_key_exists('HTTPS', $this->server) && $this->server['HTTPS'] !== 'off');
        }
    */

    /**
     * Get the original HTTP method (ignore override).
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string
     */
    /*
    public function getOriginalMethod()
    {
        return $this->method;
    }*/

    /**
     * Gets the "real" request method.
     *
     * @return string The request method
     *
     * @see getMethod()
     */
    /*
    public function getRealMethod()
    {
        return strtoupper($this->server->get('REQUEST_METHOD', 'GET'));
    }*/

    /**
     * Get request URI segment.
     *
     * @param int $index
     *
     * @return string|bool
     */
    //https://github.com/Kajna/K-Core/blob/master/Core/Http/Request.php#L185
    /*
    public function getUriSegment($index)
    {
        $segments = explode('/', $this->server->get('REQUEST_URI'));
        if (isset($segments[$index])) {
            return $segments[$index];
        }
        return false;
    }
*/

    //-----

    // TODO : parser le body selon l'encodage d'arrivée : https://github.com/slimphp/Slim/blob/3.x/Slim/Http/Request.php#L1019
    /*
    $this->registerMediaTypeParser('application/json', function ($input) {
            $result = json_decode($input, true);
            if (!is_array($result)) {
                return null;
            }
            return $result;
        });
        $this->registerMediaTypeParser('application/xml', function ($input) {
            $backup = libxml_disable_entity_loader(true);
            $backup_errors = libxml_use_internal_errors(true);
            $result = simplexml_load_string($input);
            libxml_disable_entity_loader($backup);
            libxml_clear_errors();
            libxml_use_internal_errors($backup_errors);
            if ($result === false) {
                return null;
            }
            return $result;
        });
        $this->registerMediaTypeParser('text/xml', function ($input) {
            $backup = libxml_disable_entity_loader(true);
            $backup_errors = libxml_use_internal_errors(true);
            $result = simplexml_load_string($input);
            libxml_disable_entity_loader($backup);
            libxml_clear_errors();
            libxml_use_internal_errors($backup_errors);
            if ($result === false) {
                return null;
            }
            return $result;
        });
        $this->registerMediaTypeParser('application/x-www-form-urlencoded', function ($input) {
            parse_str($input, $data);
            return $data;
        });
*/

    /**
     * Retrieve any parameters provided in the request body.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, this method MUST
     * return the contents of $_POST.
     *
     * Otherwise, this method may return any results of deserializing
     * the request body content; as parsing returns structured content, the
     * potential types MUST be arrays or objects only. A null value indicates
     * the absence of body content.
     *
     * @throws RuntimeException if the request body media type parser returns an invalid value
     *
     * @return null|array|object The deserialized body parameters, if any.
     *                           These will typically be an array or object.
     */
    /*
        public function getParsedBody()
        {
            if ($this->bodyParsed !== false) {
                return $this->bodyParsed;
            }
            if (!$this->body) {
                return null;
            }
            $mediaType = $this->getMediaType();
            // look for a media type with a structured syntax suffix (RFC 6839)
            $parts = explode('+', $mediaType);
            if (count($parts) >= 2) {
                $mediaType = 'application/' . $parts[count($parts)-1];
            }
            if (isset($this->bodyParsers[$mediaType]) === true) {
                $body = (string)$this->getBody();
                $parsed = $this->bodyParsers[$mediaType]($body);
                if (!is_null($parsed) && !is_object($parsed) && !is_array($parsed)) {
                    throw new RuntimeException(
                        'Request body media type parser return value must be an array, an object, or null'
                    );
                }
                $this->bodyParsed = $parsed;
                return $this->bodyParsed;
            }
            return null;
        }



            */

    /**
     * Get HTTP referrer.
     *
     * @return string|null
     */
    /*
    public function getReferrer()
    {
        return $this->headers->get('HTTP_REFERRER');
    }
    */
    /**
     * Get content type.
     *
     * @return string|null
     */
    /*
    public function getContentType()
    {
        return $this->headers->get('CONTENT_TYPE');
    }
    */
    /**
     * Get content length.
     *
     * @return string|null
     */
    /*
    public function getContentLength()
    {
        return $this->headers->get('CONTENT_LENGTH');
    }
    */

    /**
     * Determine if the request is sending JSON.
     *
     * @return bool
     */
    /*
    public function isJson()
    {
        //return str_contains($this->headers->get('CONTENT_TYPE'), '/json'); //Str::contains($this->header('CONTENT_TYPE'), ['/json', '+json']);
        return $this->headers->get('CONTENT_TYPE') === 'application/json';
    }
    */

    /**
     * Determine if the current request probably expects a JSON response.
     *
     * @return bool
     */
    public function expectsJson()
    {
        return ($this->isAjax() && ! $this->isPjax()) || $this->wantsJson();
    }

    /**
     * Determine if the current request is asking for JSON in return.
     *
     * @return bool
     */
    public function wantsJson()
    {
        $acceptable = $this->getAcceptableContentTypes();

        return isset($acceptable[0]) && $acceptable[0] === 'application/json'; //return isset($acceptable[0]) && Str::contains($acceptable[0], ['/json', '+json']);

/*
        'application/json',
        'application/xml',
        'text/xml',
        'text/html',


        array('txt', array('text/plain')),
        array('json', array('application/json', 'application/x-json')),
        array('jsonld', array('application/ld+json')),
        array('xml', array('text/xml', 'application/xml', 'application/x-xml')),
*/
    }

    //------------------------------------------
    // https://github.com/illuminate/http/blob/495390920e93475f14b9750b21a066a512f1c944/Concerns/InteractsWithContentTypes.php

    /**
     * Determine if the request is sending JSON.
     *
     * @return bool
     */
    public function isJson()
    {
        //return Str::contains($this->header('CONTENT_TYPE'), ['/json', '+json']);
        return mb_strpos($this->header('CONTENT_TYPE'), '/json') !== false || mb_strpos($this->header('CONTENT_TYPE'), '+json') !== false;
    }

    /**
     * Determines whether the current requests accepts a given content type.
     *
     * @param string|array $contentTypes
     *
     * @return bool
     */
    public function accepts($contentTypes)
    {
        $accepts = $this->getAcceptableContentTypes();
        if (count($accepts) === 0) {
            return true;
        }
        $types = (array) $contentTypes;
        foreach ($accepts as $accept) {
            if ($accept === '*/*' || $accept === '*') {
                return true;
            }
            foreach ($types as $type) {
                if ($this->matchesType($accept, $type) || $accept === strtok($type, '/') . '/*') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Return the most suitable content type from the given array based on content negotiation.
     *
     * @param string|array $contentTypes
     *
     * @return string|null
     */
    public function prefers($contentTypes)
    {
        $accepts = $this->getAcceptableContentTypes();
        $contentTypes = (array) $contentTypes;
        foreach ($accepts as $accept) {
            if (in_array($accept, ['*/*', '*'])) {
                return $contentTypes[0];
            }
            foreach ($contentTypes as $contentType) {
                $type = $contentType;
                if (! is_null($mimeType = $this->getMimeType($contentType))) {
                    $type = $mimeType;
                }
                if ($this->matchesType($type, $accept) || $accept === strtok($type, '/') . '/*') {
                    return $contentType;
                }
            }
        }
    }

    /**
     * Determine if the current request accepts any content type.
     *
     * @return bool
     */
    public function acceptsAnyContentType()
    {
        $acceptable = $this->getAcceptableContentTypes();

        return count($acceptable) === 0 || (
            isset($acceptable[0]) && ($acceptable[0] === '*/*' || $acceptable[0] === '*')
        );
    }

    /**
     * Determines whether a request accepts JSON.
     *
     * @return bool
     */
    public function acceptsJson()
    {
        return $this->accepts('application/json');
    }

    /**
     * Determines whether a request accepts HTML.
     *
     * @return bool
     */
    public function acceptsHtml()
    {
        return $this->accepts('text/html');
    }

    // TODO : ajouter une fonction acceptsXml()

    /**
     * Get the data format expected in the response.
     *
     * @param string $default
     *
     * @return string
     */
    public function format($default = 'html')
    {
        foreach ($this->getAcceptableContentTypes() as $type) {
            if ($format = $this->getFormat($type)) {
                return $format;
            }
        }

        return $default;
    }

    /**
     * Returns the format using the file extension.
     *
     * @return null|string
     */
    // TODO : autre exemple : https://github.com/auraphp/Aura.Accept/blob/2.x/src/Media/MediaNegotiator.php#L155
    // TODO : extrait de : https://github.com/oscarotero/psr7-middlewares/blob/master/src/Middleware/FormatNegotiator.php#L179
    // TODO : detecter le type de retour attendu par la request selon l'extension de l'url (genre http://xxx.com/api/users.json)
    /*
    private function getFromExtension(ServerRequestInterface $request)
    {
    $extension = strtolower(pathinfo($request->getUri()->getPath(), PATHINFO_EXTENSION));
    if (empty($extension)) {
        return;
    }
    foreach ($this->formats as $format => $data) {
        if (in_array($extension, $data[0], true)) {
            return $format;
        }
    }
    }*/

    //------------------------------------  END

    /**
     * Get request media type, if known.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string|null The request media type, minus content-type params
     */
    public function getMediaType1()
    {
        $contentType = $this->getContentType2();
        if ($contentType) {
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);

            return strtolower($contentTypeParts[0]);
        }
    }

    /**
     * Get request media type, if known.
     *
     * @param ServerRequestInterface $request request
     *
     * @return string|null The request media type, minus content-type params
     */
    // TODO : Méthode de Slim
    private function getMediaType2(ServerRequestInterface $request)
    {
        $parts = explode(';', $request->getHeaderLine('Content-Type'));

        return strtolower(trim(array_shift($parts)));
    }

    // TODO : récupéré de cakePHP
    private function getMediaType3(ServerRequestInterface $request)
    {
        list($type) = explode(';', $request->getHeaderLine('Content-Type'));
        $type = strtolower($type);

        return $type;
    }

    // ------------------------------------
    // https://github.com/symfony/http-foundation/blob/f5c38b8dafc947dae251045d52deaab15c3496ab/Request.php#L1203

    /**
     * Gets the format associated with the request.
     *
     * @return string|null The format (null if no content type is present)
     */
    public function getContentType1()
    {
        return $this->getFormat($this->headers->get('CONTENT_TYPE'));
    }

    /**
     * Gets the format associated with the mime type.
     *
     * @param string $mimeType The associated mime type
     *
     * @return string|null The format (null if not found)
     */
    public function getFormat($mimeType)
    {
        $canonicalMimeType = null;
        if (false !== $pos = strpos($mimeType, ';')) {
            $canonicalMimeType = substr($mimeType, 0, $pos);
        }
        if (null === static::$formats) {
            static::initializeFormats();
        }
        foreach (static::$formats as $format => $mimeTypes) {
            if (in_array($mimeType, (array) $mimeTypes)) {
                return $format;
            }
            if (null !== $canonicalMimeType && in_array($canonicalMimeType, (array) $mimeTypes)) {
                return $format;
            }
        }
    }

    /**
     * Associates a format with mime types.
     *
     * @param string       $format    The format
     * @param string|array $mimeTypes The associated mime types (the preferred one must be the first as it will be used as the content type)
     */
    public function setFormat($format, $mimeTypes)
    {
        if (null === static::$formats) {
            static::initializeFormats();
        }
        static::$formats[$format] = is_array($mimeTypes) ? $mimeTypes : [$mimeTypes];
    }

    /**
     * Initializes HTTP request formats.
     */
    protected static function initializeFormats()
    {
        // TODO : il faut pouvoir gérer le cas avec la chaine suivante => "application/*+json" :
        //https://github.com/juliangut/slim-exception/blob/master/src/Handler/ExceptionHandler.php#L140
        //https://github.com/slimphp/Slim/blob/3.x/Slim/Handlers/AbstractHandler.php#L50
        //https://github.com/symfony/http-foundation/blob/master/Request.php#L1872
        static::$formats = [
            'html'   => ['text/html', 'application/xhtml+xml'],
            'txt'    => ['text/plain'],
            'js'     => ['application/javascript', 'application/x-javascript', 'text/javascript'],
            'css'    => ['text/css'],
            'json'   => ['text/json', 'application/json', 'application/x-json'],
            'jsonld' => ['application/ld+json'],
            'xml'    => ['text/xml', 'application/xml', 'application/x-xml'],
            'rdf'    => ['application/rdf+xml'],
            'atom'   => ['application/atom+xml'],
            'rss'    => ['application/rss+xml'],
            'form'   => ['application/x-www-form-urlencoded'],
        ];
    }

    //------------------------------------- END

    /**
     * Gets the request IP address.
     *
     * @return string
     */
    public function clientIp()
    {
        return $this->getServerParam('REMOTE_ADDR');
    }

    /**
     * Get IP.
     *
     * @return string
     */
    //http://apigen.juzna.cz/doc/slimphp/Slim/source-class-Slim.Http.Request.html#35-617
    /*
    public function getIp()
    {
        $keys = array('CLIENT_IP', 'X_FORWARDED_FOR', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        foreach ($keys as $key) {
            if (isset($this->env[$key])) {
                return $this->env[$key];
            }
        }

        return $this->env['REMOTE_ADDR'];
    }


// slim founction :
function getIP()
{
    foreach (array(
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ) as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
}



 IPWARE_META_PRECEDENCE_ORDER = (
     'HTTP_X_FORWARDED_FOR', 'X_FORWARDED_FOR',  # client, proxy1, proxy2
     'HTTP_CLIENT_IP',
     'HTTP_X_REAL_IP',
     'HTTP_X_FORWARDED',
     'HTTP_X_CLUSTER_CLIENT_IP',
     'HTTP_FORWARDED_FOR',
     'HTTP_FORWARDED',
     'HTTP_VIA',
     'REMOTE_ADDR',
 )


    function getIP() {
        // The 'null' value should pass validators
        $ip = '0.0.0.0';

        // Expanded for readability
        $ipHeaders = array(
            'HTTP_X_REAL_IP',
            'X-Forwarded-For',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CLIENT_IP',
            'HTTP_VIA',
            'CF-Connecting-IP',
            'REMOTE_ADDR',
        );

        // A bit more concise
        foreach($ipHeaders as $header) {
            if (empty($_SERVER[$header])) continue;
            $ip = $_SERVER[$header];
            break;
        }

        // Simplified single IP filtering
        return explode(',', $ip)[0];
    }


    */

    /**
     * Gets the request user agent.
     *
     * @return string
     */
    public function userAgent()
    {
        return $this->headers->get('HTTP_USER_AGENT');
    }

    /**
     * Get the request's pathname.
     *
     * @return string
     */
    public function pathname()
    {
        $uri = $this->getURI();

        // Strip the query string from the URI
        $uri = strstr($uri, '?', true) ?: $uri;

        return $uri;
    }

    /**
     * Adds to or modifies the current query string.
     *
     * @param string $key   The name of the query param
     * @param mixed  $value The value of the query param
     *
     * @return string
     */
    public function query($key, $value = null)
    {
        $query = [];

        parse_str(
            $this->server()->get('QUERY_STRING'),
            $query
        );

        if (is_array($key)) {
            $query = array_merge($query, $key);
        } else {
            $query[$key] = $value;
        }

        $request_uri = $this->getURI();

        if (strpos($request_uri, '?') !== false) {
            $request_uri = strstr($request_uri, '?', true);
        }

        return $request_uri . (! empty($query) ? '?' . http_build_query($query) : null);
    }

    /**
     * Returns the content types acceptable by the end user.
     * This is determined by the `Accept` HTTP header. For example,.
     *
     * ```php
     * $_SERVER['HTTP_ACCEPT'] = 'text/plain; q=0.5, application/json; version=1.0, application/xml; version=2.0;';
     * $types = $request->getAcceptableContentTypes();
     * print_r($types);
     * // displays:
     * // [
     * //     'application/json' => ['q' => 1, 'version' => '1.0'],
     * //      'application/xml' => ['q' => 1, 'version' => '2.0'],
     * //           'text/plain' => ['q' => 0.5],
     * // ]
     * ```
     *
     * @return array the content types ordered by the quality score. Types with the highest scores
     *               will be returned first. The array keys are the content types, while the array values
     *               are the corresponding quality score and other parameters as given in the header.
     */
    //https://github.com/yiisoft/yii2/blob/master/framework/web/Request.php#L1386
    public function getAcceptableContentTypes()
    {
        if ($this->contentTypes === null) {
            if ($this->hasHeader('Accept')) {
                $this->contentTypes = $this->parseAcceptHeader($this->getHeader('Accept'));
            } else {
                $this->contentTypes = [];
            }
        }

        return $this->contentTypes;
    }

    /**
     * Returns the languages acceptable by the end user.
     * This is determined by the `Accept-Language` HTTP header.
     *
     * ex : Accept-Language: fr;q=0.9, fr-CH, en;q=0.8, de;q=0.7, *;q=0.5
     *
     * @return array the languages ordered by the preference level. The first element
     *               represents the most preferred language.
     */
    public function getAcceptableLanguages()
    {
        if ($this->languages === null) {
            if ($this->hasHeader('Accept-Language')) {
                $this->languages = array_keys($this->parseAcceptHeader($this->getHeader('Accept-Language')));
            } else {
                $this->languages = [];
            }
        }

        return $this->languages;
    }

    /**
     * Parses the given `Accept` (or `Accept-Language`) header.
     *
     * This method will return the acceptable values with their quality scores and the corresponding parameters
     * as specified in the given `Accept` header. The array keys of the return value are the acceptable values,
     * while the array values consisting of the corresponding quality scores and parameters. The acceptable
     * values with the highest quality scores will be returned first. For example,
     *
     * ```php
     * $header = 'text/plain; q=0.5, application/json; version=1.0, application/xml; version=2.0;';
     * $accepts = $request->parseAcceptHeader($header);
     * print_r($accepts);
     * // displays:
     * // [
     * //     'application/json' => ['q' => 1, 'version' => '1.0'],
     * //      'application/xml' => ['q' => 1, 'version' => '2.0'],
     * //           'text/plain' => ['q' => 0.5],
     * // ]
     * ```
     *
     * @param string $header the header to be parsed
     *
     * @return array the acceptable values ordered by their quality score. The values with the highest scores
     *               will be returned first.
     */
    protected function parseAcceptHeader($header)
    {
        $accepts = [];
        foreach (explode(',', $header) as $i => $part) {
            $params = preg_split('/\s*;\s*/', trim($part), -1, PREG_SPLIT_NO_EMPTY);
            if (empty($params)) {
                continue;
            }
            $values = [
                'q' => [$i, array_shift($params), 1],
            ];
            foreach ($params as $param) {
                if (strpos($param, '=') !== false) {
                    list($key, $value) = explode('=', $param, 2);
                    if ($key === 'q') {
                        $values['q'][2] = (float) $value;
                    } else {
                        $values[$key] = $value;
                    }
                } else {
                    $values[] = $param;
                }
            }
            $accepts[] = $values;
        }
        usort($accepts, function ($a, $b) {
            $a = $a['q']; // index, name, q
            $b = $b['q'];
            if ($a[2] > $b[2]) {
                return -1;
            }
            if ($a[2] < $b[2]) {
                return 1;
            }
            if ($a[1] === $b[1]) {
                return $a[0] > $b[0] ? 1 : -1;
            }
            if ($a[1] === '*/*') {
                return 1;
            }
            if ($b[1] === '*/*') {
                return -1;
            }
            $wa = $a[1][strlen($a[1]) - 1] === '*';
            $wb = $b[1][strlen($b[1]) - 1] === '*';
            if ($wa xor $wb) {
                return $wa ? 1 : -1;
            }

            return $a[0] > $b[0] ? 1 : -1;
        });
        $result = [];
        foreach ($accepts as $accept) {
            $name = $accept['q'][1];
            $accept['q'] = $accept['q'][2];
            $result[$name] = $accept;
        }

        return $result;
    }

    /**
     * Parse Accept* headers with qualifier options.
     *
     * Only qualifiers will be extracted, any other accept extensions will be
     * discarded as they are not frequently used.
     *
     * @param string $header header to parse
     *
     * @return array
     */
    /*
      protected function _parseAcceptWithQualifier($header)
      {
          $accept = [];
          $header = explode(',', $header);
          foreach (array_filter($header) as $value) {
              $prefValue = '1.0';
              $value = trim($value);

              $semiPos = strpos($value, ';');
              if ($semiPos !== false) {
                  $params = explode(';', $value);
                  $value = trim($params[0]);
                  foreach ($params as $param) {
                      $qPos = strpos($param, 'q=');
                      if ($qPos !== false) {
                          $prefValue = substr($param, $qPos + 2);
                      }
                  }
              }

              if (!isset($accept[$prefValue])) {
                  $accept[$prefValue] = [];
              }
              if ($prefValue) {
                  $accept[$prefValue][] = $value;
              }
          }
          krsort($accept);

          return $accept;
      }
*/

    //https://github.com/yiisoft/yii2/blob/b04ff959cec2cc46beb4e725047eb61cad58f305/framework/web/Request.php

    /**
     * Returns the relative URL for the application.
     * This is similar to [[scriptUrl]] except that it does not include the script file name,
     * and the ending slashes are removed.
     *
     * @return string the relative URL for the application
     *
     * @see setScriptUrl()
     */
    public function getBaseUrl()
    {
        //if ($this->_baseUrl === null) {
        $_baseUrl = rtrim(dirname($this->getScriptUrl()), '\\/');
        //}
        return $_baseUrl;
    }

    //https://github.com/yiisoft/yii2/blob/b04ff959cec2cc46beb4e725047eb61cad58f305/framework/web/Request.php

    /**
     * Returns the relative URL of the entry script.
     * The implementation of this method referenced Zend_Controller_Request_Http in Zend Framework.
     *
     * @throws InvalidConfigException if unable to determine the entry script URL
     *
     * @return string the relative URL of the entry script
     */
    public function getScriptUrl()
    {
        //if ($this->_scriptUrl === null) {
        $scriptFile = $_SERVER['SCRIPT_FILENAME'];
        $scriptName = basename($scriptFile);
        if (isset($_SERVER['SCRIPT_NAME']) && basename($_SERVER['SCRIPT_NAME']) === $scriptName) {
            $scriptUrl = $_SERVER['SCRIPT_NAME'];
        } elseif (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) === $scriptName) {
            $scriptUrl = $_SERVER['PHP_SELF'];
        } elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $scriptName) {
            $scriptUrl = $_SERVER['ORIG_SCRIPT_NAME'];
        } elseif (isset($_SERVER['PHP_SELF']) && ($pos = strpos($_SERVER['PHP_SELF'], '/' . $scriptName)) !== false) {
            $scriptUrl = substr($_SERVER['SCRIPT_NAME'], 0, $pos) . '/' . $scriptName;
        } elseif (! empty($_SERVER['DOCUMENT_ROOT']) && strpos($scriptFile, $_SERVER['DOCUMENT_ROOT']) === 0) {
            $scriptUrl = str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $scriptFile));
        } else {
            throw new RuntimeException('Unable to determine the entry script URL.');
        }
        //}
        //return $this->_scriptUrl;
        return $scriptUrl;
    }

    //https://github.com/symfony/symfony/blob/master/src/Symfony/Component/HttpFoundation/Request.php
    /**
     * Generates a normalized URI for the given path.
     *
     * @param string $path A path to use instead of the current one
     *
     * @return string The normalized URI for the path
     */
    // TODO : méthode à virer elle n'est plus utilisée car on ajoute dans le app->redirectTo() le baseUrl. a voir si on pêut donc virer cette fonction qui n'est plus utilisée !!!!
    /*
    public function getUriForPath($path)
    {
        // TODO : reconstruire l'url compléte !!! (coder la méthode getSchemeAndHost)
        //return $this->getSchemeAndHttpHost().$this->getBaseUrl().$path;
        return $this->getBaseUrl().$path;
    }
    */

    /**
     * Gets the Etags.
     *
     * @return array The entity tags
     */
    public function getETags()
    {
        return preg_split('/\s*,\s*/', $this->getHeaderLine('If-None-Match'), null, PREG_SPLIT_NO_EMPTY);
        //return preg_split('@\s*,\s*@', $this->getHeaderLine('If-None-Match'));
    }

    /**
     * Gets the Etags.
     *
     * @return array The entity tags
     */
    //https://github.com/yiisoft/yii2/blob/master/framework/web/Request.php#L1507
    /*
    public function getETags()
    {
        if ($this->headers->has('If-None-Match')) {
            return preg_split('/[\s,]+/', str_replace('-gzip', '', $this->headers->get('If-None-Match')), -1, PREG_SPLIT_NO_EMPTY);
        }
        return [];
    }*/

    public function getIfModifiedSince()
    {
        return strtotime($this->getHeaderLine('If-Modified-Since'));
    }

    /**
     * @return bool
     */
    public function isNoCache()
    {
        return $this->headers->hasCacheControlDirective('no-cache') || 'no-cache' == $this->headers->get('Pragma');
    }

    /**
     * Returns the request as a string.
     *
     * @return string The request
     */
    //https://github.com/symfony/http-foundation/blob/f5c38b8dafc947dae251045d52deaab15c3496ab/Request.php#L493
    public function __toString()
    {
        $output = sprintf(
            'HTTP/%s %s %s',
            $this->getProtocolVersion(),
            $this->getMethod(),
            $this->getUri()
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

    //******************************************* END ******************************************

    //*******************************************
    // https://github.com/symfony/http-foundation/blob/master/Request.php
    //******************************************* START ******************************************

    /**
     * Returns the client IP addresses.
     *
     * In the returned array the most trusted IP address is first, and the
     * least trusted one last. The "real" client IP address is the last one,
     * but this is also the least trusted one. Trusted proxies are stripped.
     *
     * Use this method carefully; you should use getClientIp() instead.
     *
     * @return array The client IP addresses
     *
     * @see getClientIp()
     */
    public function getClientIps()
    {
        $ip = $this->server->get('REMOTE_ADDR');
        if (! $this->isFromTrustedProxy()) {
            return [$ip];
        }

        return $this->getTrustedValues(self::HEADER_X_FORWARDED_FOR, $ip) ?: [$ip];
    }

    /**
     * Returns the client IP address.
     *
     * This method can read the client IP address from the "X-Forwarded-For" header
     * when trusted proxies were set via "setTrustedProxies()". The "X-Forwarded-For"
     * header value is a comma+space separated list of IP addresses, the left-most
     * being the original client, and each successive proxy that passed the request
     * adding the IP address where it received the request from.
     *
     * @return string|null The client IP address
     *
     * @see getClientIps()
     * @see http://en.wikipedia.org/wiki/X-Forwarded-For
     */
    public function getClientIp()
    {
        $ipAddresses = $this->getClientIps();

        return $ipAddresses[0];
    }

    /**
     * Returns current script name.
     *
     * @return string
     */
    public function getScriptName()
    {
        return $this->server->get('SCRIPT_NAME', $this->server->get('ORIG_SCRIPT_NAME', ''));
    }

    /**
     * Returns the path being requested relative to the executed script.
     *
     * The path info always starts with a /.
     *
     * Suppose this request is instantiated from /mysite on localhost:
     *
     *  * http://localhost/mysite              returns an empty string
     *  * http://localhost/mysite/about        returns '/about'
     *  * http://localhost/mysite/enco%20ded   returns '/enco%20ded'
     *  * http://localhost/mysite/about?var=1  returns '/about'
     *
     * @return string The raw path (i.e. not urldecoded)
     */
    public function getPathInfo()
    {
        if (null === $this->pathInfo) {
            $this->pathInfo = $this->preparePathInfo();
        }

        return $this->pathInfo;
    }

    /**
     * Returns the root path from which this request is executed.
     *
     * Suppose that an index.php file instantiates this request object:
     *
     *  * http://localhost/index.php         returns an empty string
     *  * http://localhost/index.php/page    returns an empty string
     *  * http://localhost/web/index.php     returns '/web'
     *  * http://localhost/we%20b/index.php  returns '/we%20b'
     *
     * @return string The raw path (i.e. not urldecoded)
     */
    /*
    public function getBasePath()
    {
        if (null === $this->basePath) {
            $this->basePath = $this->prepareBasePath();
        }
        return $this->basePath;
    }*/
    /**
     * Returns the root URL from which this request is executed.
     *
     * The base URL never ends with a /.
     *
     * This is similar to getBasePath(), except that it also includes the
     * script filename (e.g. index.php) if one exists.
     *
     * @return string The raw URL (i.e. not urldecoded)
     */
    /*
    public function getBaseUrl()
    {
        if (null === $this->baseUrl) {
            $this->baseUrl = $this->prepareBaseUrl();
        }
        return $this->baseUrl;
    }*/

    /**
     * Returns the port on which the request is made.
     *
     * This method can read the client port from the "X-Forwarded-Port" header
     * when trusted proxies were set via "setTrustedProxies()".
     *
     * The "X-Forwarded-Port" header must contain the client port.
     *
     * @return int|string can be a string if fetched from the server bag
     */
    /*
    public function getPort()
    {
        if ($this->isFromTrustedProxy() && $host = $this->getTrustedValues(self::HEADER_X_FORWARDED_PORT)) {
            $host = $host[0];
        } elseif ($this->isFromTrustedProxy() && $host = $this->getTrustedValues(self::HEADER_X_FORWARDED_HOST)) {
            $host = $host[0];
        } elseif (!$host = $this->headers->get('HOST')) {
            return $this->server->get('SERVER_PORT');
        }
        if ('[' === $host[0]) {
            $pos = strpos($host, ':', strrpos($host, ']'));
        } else {
            $pos = strrpos($host, ':');
        }
        if (false !== $pos) {
            return (int) substr($host, $pos + 1);
        }
        return 'https' === $this->getScheme() ? 443 : 80;
    }*/

    /**
     * Returns the user.
     *
     * @return string|null
     */
    public function getUser()
    {
        return $this->headers->get('PHP_AUTH_USER');
    }

    /**
     * Returns the password.
     *
     * @return string|null
     */
    public function getPassword()
    {
        return $this->headers->get('PHP_AUTH_PW');
    }

    /**
     * Gets the user info.
     *
     * @return string A user name and, optionally, scheme-specific information about how to gain authorization to access the server
     */
    public function getUserInfo()
    {
        $userinfo = $this->getUser();
        $pass = $this->getPassword();
        if ('' != $pass) {
            $userinfo .= ":$pass";
        }

        return $userinfo;
    }

    /**
     * Returns the HTTP host being requested.
     *
     * The port name will be appended to the host if it's non-standard.
     *
     * @return string
     */
    public function getHttpHost()
    {
        $scheme = $this->uri->getScheme();
        $port = $this->uri->getPort();
        if (('http' == $scheme && 80 == $port) || ('https' == $scheme && 443 == $port)) {
            return $this->uri->getHost();
        }

        return $this->uri->getHost() . ':' . $port;
    }

    /**
     * Returns the requested URI (path and query string).
     *
     * @return string The raw URI (i.e. not URI decoded)
     */
    public function getRequestUri()
    {
        if (null === $this->requestUri) {
            $this->requestUri = $this->prepareRequestUri();
        }

        return $this->requestUri;
    }

    /**
     * Gets the scheme and HTTP host.
     *
     * If the URL was called with basic authentication, the user
     * and the password are not added to the generated string.
     *
     * @return string The scheme and HTTP host
     */
    public function getSchemeAndHttpHost()
    {
        return $this->uri->getScheme() . '://' . $this->getHttpHost();
    }

    /**
     * Generates a normalized URI (URL) for the Request.
     *
     * @return string A normalized URI (URL) for the Request
     *
     * @see getQueryString()
     */
    /*
    public function getUri()
    {
        if (null !== $qs = $this->getQueryString()) {
            $qs = '?'.$qs;
        }
        return $this->getSchemeAndHttpHost().$this->getBaseUrl().$this->getPathInfo().$qs;
    }*/

    /**
     * Generates a normalized URI for the given path.
     *
     * @param string $path A path to use instead of the current one
     *
     * @return string The normalized URI for the path
     */
    public function getUriForPath($path)
    {
        return $this->getSchemeAndHttpHost() . $this->getBaseUrl() . $path;
    }

    /**
     * Returns the path as relative reference from the current Request path.
     *
     * Only the URIs path component (no schema, host etc.) is relevant and must be given.
     * Both paths must be absolute and not contain relative parts.
     * Relative URLs from one resource to another are useful when generating self-contained downloadable document archives.
     * Furthermore, they can be used to reduce the link size in documents.
     *
     * Example target paths, given a base path of "/a/b/c/d":
     * - "/a/b/c/d"     -> ""
     * - "/a/b/c/"      -> "./"
     * - "/a/b/"        -> "../"
     * - "/a/b/c/other" -> "other"
     * - "/a/x/y"       -> "../../x/y"
     *
     * @param string $path The target path
     *
     * @return string The relative target path
     */
    public function getRelativeUriForPath($path)
    {
        // be sure that we are dealing with an absolute path
        if (! isset($path[0]) || '/' !== $path[0]) {
            return $path;
        }
        if ($path === $basePath = $this->getPathInfo()) {
            return '';
        }
        $sourceDirs = explode('/', isset($basePath[0]) && '/' === $basePath[0] ? substr($basePath, 1) : $basePath);
        $targetDirs = explode('/', isset($path[0]) && '/' === $path[0] ? substr($path, 1) : $path);
        array_pop($sourceDirs);
        $targetFile = array_pop($targetDirs);
        foreach ($sourceDirs as $i => $dir) {
            if (isset($targetDirs[$i]) && $dir === $targetDirs[$i]) {
                unset($sourceDirs[$i], $targetDirs[$i]);
            } else {
                break;
            }
        }
        $targetDirs[] = $targetFile;
        $path = str_repeat('../', count($sourceDirs)) . implode('/', $targetDirs);
        // A reference to the same base directory or an empty subdirectory must be prefixed with "./".
        // This also applies to a segment with a colon character (e.g., "file:colon") that cannot be used
        // as the first segment of a relative-path reference, as it would be mistaken for a scheme name
        // (see http://tools.ietf.org/html/rfc3986#section-4.2).
        return ! isset($path[0]) || '/' === $path[0]
            || false !== ($colonPos = strpos($path, ':')) && ($colonPos < ($slashPos = strpos($path, '/')) || false === $slashPos)
            ? "./$path" : $path;
    }

    /**
     * Checks whether the request is secure or not.
     *
     * This method can read the client protocol from the "X-Forwarded-Proto" header
     * when trusted proxies were set via "setTrustedProxies()".
     *
     * The "X-Forwarded-Proto" header must contain the protocol: "https" or "http".
     *
     * @return bool
     */
    /*
    public function isSecure()
    {
        if ($this->isFromTrustedProxy() && $proto = $this->getTrustedValues(self::HEADER_X_FORWARDED_PROTO)) {
            return in_array(strtolower($proto[0]), array('https', 'on', 'ssl', '1'), true);
        }
        $https = $this->server->get('HTTPS');
        return !empty($https) && 'off' !== strtolower($https);
    }*/

    /**
     * Gets the request "intended" method.
     *
     * If the X-HTTP-Method-Override header is set, and if the method is a POST,
     * then it is used to determine the "real" intended HTTP method.
     *
     * The _method request parameter can also be used to determine the HTTP method,
     * but only if enableHttpMethodParameterOverride() has been called.
     *
     * The method is always an uppercased string.
     *
     * @return string The request method
     *
     * @see getRealMethod()
     */
    /*
    public function getMethod()
    {
        if (null === $this->method) {
            $this->method = strtoupper($this->server->get('REQUEST_METHOD', 'GET'));
            if ('POST' === $this->method) {
                if ($method = $this->headers->get('X-HTTP-METHOD-OVERRIDE')) {
                    $this->method = strtoupper($method);
                } elseif (self::$httpMethodParameterOverride) {
                    $this->method = strtoupper($this->request->get('_method', $this->query->get('_method', 'POST')));
                }
            }
        }
        return $this->method;
    }*/

    /**
     * Gets the "real" request method.
     *
     * @return string The request method
     *
     * @see getMethod()
     */
    public function getRealMethod()
    {
        return strtoupper($this->server->get('REQUEST_METHOD', 'GET'));
    }

    /**
     * Gets the mime type associated with the format.
     *
     * @param string $format The format
     *
     * @return string The associated mime type (null if not found)
     */
    public function getMimeType($format)
    {
        if (null === static::$formats) {
            static::initializeFormats();
        }

        return isset(static::$formats[$format]) ? static::$formats[$format][0] : null;
    }

    /**
     * Gets the mime types associated with the format.
     *
     * @param string $format The format
     *
     * @return array The associated mime types
     */
    public static function getMimeTypes($format)
    {
        if (null === static::$formats) {
            static::initializeFormats();
        }

        return isset(static::$formats[$format]) ? static::$formats[$format] : [];
    }

    /**
     * Gets the request format.
     *
     * Here is the process to determine the format:
     *
     *  * format defined by the user (with setRequestFormat())
     *  * _format request attribute
     *  * $default
     *
     * @param string $default The default format
     *
     * @return string The request format
     */
    public function getRequestFormat($default = 'html')
    {
        if (null === $this->format) {
            $this->format = $this->attributes->get('_format');
        }

        return null === $this->format ? $default : $this->format;
    }

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $defaultLocale = 'en';

    /**
     * Get the default locale.
     *
     * @return string
     */
    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }

    /**
     * Sets the default locale.
     *
     * @param string $locale
     */
    public function setDefaultLocale($locale)
    {
        $this->defaultLocale = $locale;
        if (null === $this->locale) {
            $this->setPhpDefaultLocale($locale);
        }
    }

    /**
     * Sets the locale.
     *
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->setPhpDefaultLocale($this->locale = $locale);
    }

    /**
     * Get the locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return null === $this->locale ? $this->defaultLocale : $this->locale;
    }

    private function setPhpDefaultLocale(string $locale)
    {
        // if either the class Locale doesn't exist, or an exception is thrown when
        // setting the default locale, the intl module is not installed, and
        // the call can be ignored:
        try {
            if (class_exists('Locale', false)) {
                \Locale::setDefault($locale);
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * Checks whether or not the method is safe.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.2.1
     *
     * @param bool $andCacheable Adds the additional condition that the method should be cacheable. True by default.
     *
     * @return bool
     */
    public function isMethodSafe(/* $andCacheable = true */)
    {
        if (! func_num_args() || func_get_arg(0)) {
            // setting $andCacheable to false should be deprecated in 4.1
            throw new \BadMethodCallException('Checking only for cacheable HTTP methods with Symfony\Component\HttpFoundation\Request::isMethodSafe() is not supported.');
        }

        return in_array($this->getMethod(), ['GET', 'HEAD', 'OPTIONS', 'TRACE']);
    }

    /**
     * Checks whether or not the method is idempotent.
     *
     * @return bool
     */
    public function isMethodIdempotent()
    {
        return in_array($this->getMethod(), ['HEAD', 'GET', 'PUT', 'DELETE', 'TRACE', 'OPTIONS', 'PURGE']);
    }

    /**
     * Checks whether the method is cacheable or not.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.2.3
     *
     * @return bool
     */
    public function isMethodCacheable()
    {
        return in_array($this->getMethod(), ['GET', 'HEAD']);
    }

    /**
     * Returns the protocol version.
     *
     * If the application is behind a proxy, the protocol version used in the
     * requests between the client and the proxy and between the proxy and the
     * server might be different. This returns the former (from the "Via" header)
     * if the proxy is trusted (see "setTrustedProxies()"), otherwise it returns
     * the latter (from the "SERVER_PROTOCOL" server parameter).
     *
     * @return string
     */
    /*
    public function getProtocolVersion2()
    {
        if ($this->isFromTrustedProxy()) {
            preg_match('~^(HTTP/)?([1-9]\.[0-9]) ~', $this->headers->get('Via'), $matches);
            if ($matches) {
                return 'HTTP/'.$matches[2];
            }
        }
        return $this->server->get('SERVER_PROTOCOL');
    }*/

    /**
     * Returns the preferred language.
     *
     * @param array $locales An array of ordered available locales
     *
     * @return string|null The preferred locale
     */
    public function getPreferredLanguage(array $locales = null)
    {
        $preferredLanguages = $this->getLanguages();
        if (empty($locales)) {
            return isset($preferredLanguages[0]) ? $preferredLanguages[0] : null;
        }
        if (! $preferredLanguages) {
            return $locales[0];
        }
        $extendedPreferredLanguages = [];
        foreach ($preferredLanguages as $language) {
            $extendedPreferredLanguages[] = $language;
            if (false !== $position = strpos($language, '_')) {
                $superLanguage = substr($language, 0, $position);
                if (! in_array($superLanguage, $preferredLanguages)) {
                    $extendedPreferredLanguages[] = $superLanguage;
                }
            }
        }
        $preferredLanguages = array_values(array_intersect($extendedPreferredLanguages, $locales));

        return isset($preferredLanguages[0]) ? $preferredLanguages[0] : $locales[0];
    }

    /**
     * Gets a list of languages acceptable by the client browser.
     *
     * @return array Languages ordered in the user browser preferences
     */
    public function getLanguages()
    {
        if (null !== $this->languages) {
            return $this->languages;
        }
        $languages = AcceptHeader::fromString($this->headers->get('Accept-Language'))->all();
        $this->languages = [];
        foreach ($languages as $lang => $acceptHeaderItem) {
            if (false !== strpos($lang, '-')) {
                $codes = explode('-', $lang);
                if ('i' === $codes[0]) {
                    // Language not listed in ISO 639 that are not variants
                    // of any listed language, which can be registered with the
                    // i-prefix, such as i-cherokee
                    if (count($codes) > 1) {
                        $lang = $codes[1];
                    }
                } else {
                    for ($i = 0, $max = count($codes); $i < $max; $i++) {
                        if (0 === $i) {
                            $lang = strtolower($codes[0]);
                        } else {
                            $lang .= '_' . strtoupper($codes[$i]);
                        }
                    }
                }
            }
            $this->languages[] = $lang;
        }

        return $this->languages;
    }

    /**
     * Gets a list of charsets acceptable by the client browser.
     *
     * @return array List of charsets in preferable order
     */
    public function getCharsets()
    {
        if (null !== $this->charsets) {
            return $this->charsets;
        }

        return $this->charsets = array_keys(AcceptHeader::fromString($this->headers->get('Accept-Charset'))->all());
    }

    /**
     * Gets a list of encodings acceptable by the client browser.
     *
     * @return array List of encodings in preferable order
     */
    public function getEncodings()
    {
        if (null !== $this->encodings) {
            return $this->encodings;
        }

        return $this->encodings = array_keys(AcceptHeader::fromString($this->headers->get('Accept-Encoding'))->all());
    }

    /**
     * Returns true if the request is a XMLHttpRequest.
     *
     * It works if your JavaScript library sets an X-Requested-With HTTP header.
     * It is known to work with common JavaScript frameworks:
     *
     * @see http://en.wikipedia.org/wiki/List_of_Ajax_frameworks#JavaScript
     *
     * @return bool true if the request is an XMLHttpRequest, false otherwise
     */
    public function isXmlHttpRequest()
    {
        return 'XMLHttpRequest' == $this->headers->get('X-Requested-With');
    }

    //******************************************* END ******************************************

    //*******************************************
    // https://github.com/yiisoft/yii2/blob/master/framework/web/Request.php
    //******************************************* START ******************************************

    /**
     * Returns the schema and host part of the current request URL.
     *
     * The returned URL does not have an ending slash.
     *
     * By default this value is based on the user request information. This method will
     * return the value of `$_SERVER['HTTP_HOST']` if it is available or `$_SERVER['SERVER_NAME']` if not.
     * You may want to check out the [PHP documentation](http://php.net/manual/en/reserved.variables.server.php)
     * for more information on these variables.
     *
     * You may explicitly specify it by setting the [[setHostInfo()|hostInfo]] property.
     *
     * > Warning: Dependent on the server configuration this information may not be
     * > reliable and [may be faked by the user sending the HTTP request](https://www.acunetix.com/vulnerabilities/web/host-header-attack).
     * > If the webserver is configured to serve the same site independent of the value of
     * > the `Host` header, this value is not reliable. In such situations you should either
     * > fix your webserver configuration or explicitly set the value by setting the [[setHostInfo()|hostInfo]] property.
     * > If you don't have access to the server configuration, you can setup [[\yii\filters\HostControl]] filter at
     * > application level in order to protect against such kind of attack.
     *
     * @property string|null schema and hostname part (with port number if needed) of the request URL
     * (e.g. `http://www.yiiframework.com`), null if can't be obtained from `$_SERVER` and wasn't set.
     * See [[getHostInfo()]] for security related notes on this property.
     *
     * @return string|null schema and hostname part (with port number if needed) of the request URL
     *                     (e.g. `http://www.yiiframework.com`), null if can't be obtained from `$_SERVER` and wasn't set.
     *
     * @see setHostInfo()
     */
    public function getHostInfo()
    {
        if ($this->_hostInfo === null) {
            $secure = $this->getIsSecureConnection();
            $http = $secure ? 'https' : 'http';
            if ($this->headers->has('X-Forwarded-Host')) {
                $this->_hostInfo = $http . '://' . $this->headers->get('X-Forwarded-Host');
            } elseif ($this->headers->has('Host')) {
                $this->_hostInfo = $http . '://' . $this->headers->get('Host');
            } elseif (isset($_SERVER['SERVER_NAME'])) {
                $this->_hostInfo = $http . '://' . $_SERVER['SERVER_NAME'];
                $port = $secure ? $this->getSecurePort() : $this->getPort();
                if (($port !== 80 && ! $secure) || ($port !== 443 && $secure)) {
                    $this->_hostInfo .= ':' . $port;
                }
            }
        }

        return $this->_hostInfo;
    }

    /**
     * @var array list of headers to check for determining whether the connection is made via HTTPS.
     *            The array keys are header names and the array value is a list of header values that indicate a secure connection.
     *            The match of header names and values is case-insensitive.
     *            It's not advisable to put insecure headers here.
     *
     * @see $trustedHosts
     * @see $secureHeaders
     * @since 2.0.13
     */
    public $secureProtocolHeaders = [
        'X-Forwarded-Proto' => ['https'], // Common
        'Front-End-Https'   => ['on'], // Microsoft
    ];

    /**
     * @var string[] List of headers where proxies store the real client IP.
     *               It's not advisable to put insecure headers here.
     *               The match of header names is case-insensitive.
     *
     * @see $trustedHosts
     * @see $secureHeaders
     * @since 2.0.13
     */
    public $ipHeaders = [
        'X-Forwarded-For', // Common
    ];

    /**
     * Return if the request is sent via secure channel (https).
     *
     * @return bool if the request is sent via secure channel (https)
     */
    public function getIsSecureConnection()
    {
        if (isset($_SERVER['HTTPS']) && (strcasecmp($_SERVER['HTTPS'], 'on') === 0 || $_SERVER['HTTPS'] == 1)) {
            return true;
        }
        foreach ($this->secureProtocolHeaders as $header => $values) {
            if (($headerValue = $this->headers->get($header, null)) !== null) {
                foreach ($values as $value) {
                    if (strcasecmp($headerValue, $value) === 0) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Returns the server name.
     *
     * @return string server name, null if not available
     */
    public function getServerName()
    {
        return isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : null;
    }

    /**
     * Returns the server port number.
     *
     * @return int|null server port number, null if not available
     */
    public function getServerPort()
    {
        return isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : null;
    }

    /**
     * Returns the URL referrer.
     *
     * @return string|null URL referrer, null if not available
     */
    public function getReferrer()
    {
        return $this->headers->get('Referer');
    }

    /**
     * Returns the URL origin of a CORS request.
     *
     * The return value is taken from the `Origin` [[getHeaders()|header]] sent by the browser.
     *
     * Note that the origin request header indicates where a fetch originates from.
     * It doesn't include any path information, but only the server name.
     * It is sent with a CORS requests, as well as with POST requests.
     * It is similar to the referer header, but, unlike this header, it doesn't disclose the whole path.
     * Please refer to <https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Origin> for more information.
     *
     * @return string|null URL origin of a CORS request, `null` if not available
     *
     * @see getHeaders()
     * @since 2.0.13
     */
    public function getOrigin()
    {
        return $this->getHeaders()->get('origin');
    }

    /**
     * Returns the user agent.
     *
     * @return string|null user agent, null if not available
     */
    public function getUserAgent()
    {
        return $this->headers->get('User-Agent');
    }

    /**
     * Returns the user IP address.
     * The IP is determined using headers and / or `$_SERVER` variables.
     *
     * @return string|null user IP address, null if not available
     */
    public function getUserIP()
    {
        foreach ($this->ipHeaders as $ipHeader) {
            if ($this->headers->has($ipHeader)) {
                return trim(explode(',', $this->headers->get($ipHeader))[0]);
            }
        }

        return $this->getRemoteIP();
    }

    /**
     * Returns the user host name.
     * The HOST is determined using headers and / or `$_SERVER` variables.
     *
     * @return string|null user host name, null if not available
     */
    public function getUserHost()
    {
        foreach ($this->ipHeaders as $ipHeader) {
            if ($this->headers->has($ipHeader)) {
                return gethostbyaddr(trim(explode(',', $this->headers->get($ipHeader))[0]));
            }
        }

        return $this->getRemoteHost();
    }

    /**
     * Returns the IP on the other end of this connection.
     * This is always the next hop, any headers are ignored.
     *
     * @return string|null remote IP address, `null` if not available
     *
     * @since 2.0.13
     */
    public function getRemoteIP()
    {
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
    }

    /**
     * Returns the host name of the other end of this connection.
     * This is always the next hop, any headers are ignored.
     *
     * @return string|null remote host name, `null` if not available
     *
     * @see getUserHost()
     * @see getRemoteIP()
     * @since 2.0.13
     */
    public function getRemoteHost()
    {
        return isset($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : null;
    }

    /**
     * @return string|null the username sent via HTTP authentication, `null` if the username is not given
     *
     * @see getAuthCredentials() to get both username and password in one call
     */
    public function getAuthUser()
    {
        return $this->getAuthCredentials()[0];
    }

    /**
     * @return string|null the password sent via HTTP authentication, `null` if the password is not given
     *
     * @see getAuthCredentials() to get both username and password in one call
     */
    public function getAuthPassword()
    {
        return $this->getAuthCredentials()[1];
    }

    /**
     * @return array that contains exactly two elements:
     *               - 0: the username sent via HTTP authentication, `null` if the username is not given
     *               - 1: the password sent via HTTP authentication, `null` if the password is not given
     *
     * @see getAuthUser() to get only username
     * @see getAuthPassword() to get only password
     * @since 2.0.13
     */
    public function getAuthCredentials()
    {
        $username = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : null;
        $password = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : null;
        if ($username !== null || $password !== null) {
            return [$username, $password];
        }
        /*
         * Apache with php-cgi does not pass HTTP Basic authentication to PHP by default.
         * To make it work, add the following line to to your .htaccess file:
         *
         * RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
         */
        $auth_token = $this->getHeaders()->get('HTTP_AUTHORIZATION') ?: $this->getHeaders()->get('REDIRECT_HTTP_AUTHORIZATION');
        if ($auth_token !== null && strpos(strtolower($auth_token), 'basic') === 0) {
            $parts = array_map(function ($value) {
                return strlen($value) === 0 ? null : $value;
            }, explode(':', base64_decode(mb_substr($auth_token, 6)), 2));
            if (count($parts) < 2) {
                return [$parts[0], null];
            }

            return $parts;
        }

        return [null, null];
    }

    private $_port;

    /**
     * Returns the port to use for insecure requests.
     * Defaults to 80, or the port specified by the server if the current
     * request is insecure.
     *
     * @return int port number for insecure requests
     *
     * @see setPort()
     */
    /*
    public function getPort()
    {
        if ($this->_port === null) {
            $serverPort = $this->getServerPort();
            $this->_port = !$this->getIsSecureConnection() && $serverPort !== null ? $serverPort : 80;
        }
        return $this->_port;
    }*/

    /**
     * Returns the port to use for secure requests.
     * Defaults to 443, or the port specified by the server if the current
     * request is secure.
     *
     * @return int port number for secure requests
     *
     * @see setSecurePort()
     */
    public function getSecurePort()
    {
        if ($this->_securePort === null) {
            $serverPort = $this->getServerPort();
            $this->_securePort = $this->getIsSecureConnection() && $serverPort !== null ? $serverPort : 443;
        }

        return $this->_securePort;
    }

    /**
     * Returns the cookie collection.
     *
     * Through the returned cookie collection, you may access a cookie using the following syntax:
     *
     * ```php
     * $cookie = $request->cookies['name']
     * if ($cookie !== null) {
     *     $value = $cookie->value;
     * }
     *
     * // alternatively
     * $value = $request->cookies->getValue('name');
     * ```
     *
     * @return CookieCollection the cookie collection
     */
    public function getCookies()
    {
        if ($this->_cookies === null) {
            $this->_cookies = new CookieCollection($this->loadCookies(), [
                'readOnly' => true,
            ]);
        }

        return $this->_cookies;
    }

    /**
     * Converts `$_COOKIE` into an array of [[Cookie]].
     *
     * @throws InvalidConfigException if [[cookieValidationKey]] is not set when [[enableCookieValidation]] is true
     *
     * @return array the cookies obtained from request
     */
    protected function loadCookies()
    {
        $cookies = [];
        if ($this->enableCookieValidation) {
            if ($this->cookieValidationKey == '') {
                throw new InvalidConfigException(get_class($this) . '::cookieValidationKey must be configured with a secret key.');
            }
            foreach ($_COOKIE as $name => $value) {
                if (! is_string($value)) {
                    continue;
                }
                $data = Yii::$app->getSecurity()->validateData($value, $this->cookieValidationKey);
                if ($data === false) {
                    continue;
                }
                $data = @unserialize($data);
                if (is_array($data) && isset($data[0], $data[1]) && $data[0] === $name) {
                    $cookies[$name] = Yii::createObject([
                        'class'  => 'yii\web\Cookie',
                        'name'   => $name,
                        'value'  => $data[1],
                        'expire' => null,
                    ]);
                }
            }
        } else {
            foreach ($_COOKIE as $name => $value) {
                $cookies[$name] = Yii::createObject([
                    'class'  => 'yii\web\Cookie',
                    'name'   => $name,
                    'value'  => $value,
                    'expire' => null,
                ]);
            }
        }

        return $cookies;
    }

    private $_csrfToken;

    /**
     * Returns the token used to perform CSRF validation.
     *
     * This token is generated in a way to prevent [BREACH attacks](http://breachattack.com/). It may be passed
     * along via a hidden field of an HTML form or an HTTP header value to support CSRF validation.
     *
     * @param bool $regenerate whether to regenerate CSRF token. When this parameter is true, each time
     *                         this method is called, a new CSRF token will be generated and persisted (in session or cookie).
     *
     * @return string the token used to perform CSRF validation
     */
    public function getCsrfToken($regenerate = false)
    {
        if ($this->_csrfToken === null || $regenerate) {
            $token = $this->loadCsrfToken();
            if ($regenerate || empty($token)) {
                $token = $this->generateCsrfToken();
            }
            $this->_csrfToken = Yii::$app->security->maskToken($token);
        }

        return $this->_csrfToken;
    }

    /**
     * Loads the CSRF token from cookie or session.
     *
     * @return string the CSRF token loaded from cookie or session. Null is returned if the cookie or session
     *                does not have CSRF token.
     */
    protected function loadCsrfToken()
    {
        if ($this->enableCsrfCookie) {
            return $this->getCookies()->getValue($this->csrfParam);
        }

        return Yii::$app->getSession()->get($this->csrfParam);
    }

    /**
     * Generates an unmasked random token used to perform CSRF validation.
     *
     * @return string the random token for CSRF validation
     */
    protected function generateCsrfToken()
    {
        $token = Yii::$app->getSecurity()->generateRandomString();
        if ($this->enableCsrfCookie) {
            $cookie = $this->createCsrfCookie($token);
            Yii::$app->getResponse()->getCookies()->add($cookie);
        } else {
            Yii::$app->getSession()->set($this->csrfParam, $token);
        }

        return $token;
    }

    /**
     * @return string the CSRF token sent via [[CSRF_HEADER]] by browser. Null is returned if no such header is sent.
     */
    public function getCsrfTokenFromHeader()
    {
        return $this->headers->get(static::CSRF_HEADER);
    }

    /**
     * Creates a cookie with a randomly generated CSRF token.
     * Initial values specified in [[csrfCookie]] will be applied to the generated cookie.
     *
     * @param string $token the CSRF token
     *
     * @return Cookie the generated cookie
     *
     * @see enableCsrfValidation
     */
    protected function createCsrfCookie($token)
    {
        $options = $this->csrfCookie;

        return Yii::createObject(array_merge($options, [
            'class' => 'yii\web\Cookie',
            'name'  => $this->csrfParam,
            'value' => $token,
        ]));
    }

    /**
     * Performs the CSRF validation.
     *
     * This method will validate the user-provided CSRF token by comparing it with the one stored in cookie or session.
     * This method is mainly called in [[Controller::beforeAction()]].
     *
     * Note that the method will NOT perform CSRF validation if [[enableCsrfValidation]] is false or the HTTP method
     * is among GET, HEAD or OPTIONS.
     *
     * @param string $clientSuppliedToken the user-provided CSRF token to be validated. If null, the token will be retrieved from
     *                                    the [[csrfParam]] POST field or HTTP header.
     *                                    This parameter is available since version 2.0.4.
     *
     * @return bool whether CSRF token is valid. If [[enableCsrfValidation]] is false, this method will return true.
     */
    public function validateCsrfToken($clientSuppliedToken = null)
    {
        $method = $this->getMethod();
        // only validate CSRF token on non-"safe" methods https://tools.ietf.org/html/rfc2616#section-9.1.1
        if (! $this->enableCsrfValidation || in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return true;
        }
        $trueToken = $this->getCsrfToken();
        if ($clientSuppliedToken !== null) {
            return $this->validateCsrfTokenInternal($clientSuppliedToken, $trueToken);
        }

        return $this->validateCsrfTokenInternal($this->getBodyParam($this->csrfParam), $trueToken)
            || $this->validateCsrfTokenInternal($this->getCsrfTokenFromHeader(), $trueToken);
    }

    /**
     * Validates CSRF token.
     *
     * @param string $clientSuppliedToken the masked client-supplied token
     * @param string $trueToken           the masked true token
     *
     * @return bool
     */
    private function validateCsrfTokenInternal($clientSuppliedToken, $trueToken)
    {
        if (! is_string($clientSuppliedToken)) {
            return false;
        }
        $security = Yii::$app->security;

        return $security->unmaskToken($clientSuppliedToken) === $security->unmaskToken($trueToken);
    }

    //https://github.com/yiisoft/yii/blob/master/framework/base/CSecurityManager.php#L626

    /**
     * Masks a token to make it uncompressible.
     * Applies a random mask to the token and prepends the mask used to the result making the string always unique.
     * Used to mitigate BREACH attack by randomizing how token is outputted on each request.
     *
     * @param string $token An unmasked token.
     *
     * @return string A masked token.
     *
     * @since 1.1.18
     */
    public function maskToken($token)
    {
        // The number of bytes in a mask is always equal to the number of bytes in a token.
        $mask = $this->generateRandomString($this->strlen($token));

        return strtr(base64_encode($mask . ($mask ^ $token)), '+/', '-_');
    }

    /**
     * Unmasks a token previously masked by `maskToken`.
     *
     * @param string $maskedToken A masked token.
     *
     * @return string An unmasked token, or an empty string in case of token format is invalid.
     *
     * @since 1.1.18
     */
    public function unmaskToken($maskedToken)
    {
        $decoded = base64_decode(strtr($maskedToken, '-_', '+/'));
        $length = $this->strlen($decoded) / 2;
        // Check if the masked token has an even length.
        if (! is_int($length)) {
            return '';
        }

        return $this->substr($decoded, $length, $length) ^ $this->substr($decoded, 0, $length);
    }

    //******************************************* END ******************************************
}
