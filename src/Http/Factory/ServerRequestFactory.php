<?php

declare(strict_types=1);

namespace Chiron\Http\Factory;

/*
require_once __DIR__ . '/../../../../vendor/nyholm/psr7/src/Uri.php';
*/

//github.com/http-interop/http-factory-diactoros/blob/master/src/ServerRequestFactory.php
//https://github.com/http-interop/http-factory-guzzle/blob/master/src/ServerRequestFactory.php
// https://github.com/http-interop/http-factory-slim/blob/master/src/ServerRequestFactory.php

// TODO : utiliser l'interface PSR17 : https://github.com/http-interop/http-factory/blob/master/src/ServerRequestFactoryInterface.php

// https://github.com/viserio/http-factory/blob/master/ServerRequestFactory.php
// https://github.com/zendframework/zend-diactoros/blob/master/src/ServerRequestFactory.php
// https://github.com/Wandu/Http/blob/master/Factory/ServerRequestFactory.php

//https://github.com/Hail-Team/framework/blob/fcd26224a6d175458df249b74bf03c88b5321840/src/Http/Helpers.php

//namespace Viserio\Component\HttpFactory;

use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\UploadedFile;
use Chiron\Http\Psr\Uri;
use Interop\Http\Factory\ServerRequestFactoryInterface;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

//use Nyholm\Psr7\Factory\ServerRequestFactory as ServerRequestFactoryPsr17;

// basé sur : https://github.com/viserio/http-factory/blob/master/ServerRequestFactory.php

class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * Function to use to get apache request headers; present only to simplify mocking.
     *
     * @var callable
     */
    private static $apacheRequestHeaders = 'apache_request_headers';

    /**
     * {@inheritdoc}
     */
    public function createServerRequest($method, $uri): ServerRequestInterface
    {
        //return $this->buildServerRequest($method, $uri);
        return new ServerRequest($method, $uri);
    }

    /**
     * {@inheritdoc}
     */
    public function createServerRequestFromArray(array $server): ServerRequestInterface
    {
        // Check if request is valid, need URI and method set at least.
        if (! isset($server['REQUEST_URI'])) {
            throw new InvalidArgumentException('HTTP request must have an URI set.');
        }
        if (! isset($server['REQUEST_METHOD'])) {
            throw new InvalidArgumentException('HTTP request must have an HTTP method set.');
        }

        // TODO : vérifier si c'est vraiment utile
        // Fix URI if needed.
        /*
        if (strpos($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']) === 0) {
            $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], strlen($_SERVER['SCRIPT_NAME']));
        } elseif (strpos($_SERVER['REQUEST_URI'], dirname($_SERVER['SCRIPT_NAME'])) === 0) {
            $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], strlen(dirname($_SERVER['SCRIPT_NAME'])));
        }
        if (!empty($_SERVER['QUERY_STRING'])) {
            $_SERVER['REQUEST_URI'] = str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
        }
        $_SERVER['REQUEST_URI'] = trim($_SERVER['REQUEST_URI'], '/');
*/

        // TODO : gérer le cas ou la fonction getallheader qui est un alias de apache n'est pas présente. Dans ce cas il faut itérer sur $_SERVER pour récupérer les headers !!!!
        // TODO : cf : https://github.com/slimphp/Slim/blob/3.x/Slim/Http/Headers.php#L54   et les polyfill : http://php.net/manual/fr/function.getallheaders.php   +   https://github.com/ralouphie/getallheaders/blob/master/src/getallheaders.php    +    http://php.net/manual/fr/function.apache-request-headers.php
//        $headers = function_exists('getallheaders') ? getallheaders() : $this->marshalHeaders($server);

        $server = $this->normalizeServer($server);
        $headers = $this->marshalHeaders($server);

        /*
        // TODO : se passer du getallheaders : https://github.com/zendframework/zend-diactoros/blob/fb7f06e1b78c2aa17d08f30633bb2fa337428182/src/ServerRequestFactory.php#L196
        //https://github.com/slimphp/Slim/blob/d28272231017ae737abeee4b99673cbf29ee4e69/Slim/Http/Headers.php#L51
        //https://github.com/Kajna/K-Core/blob/master/Core/Http/Request.php#L108
                $specialHeaders = ['CONTENT_TYPE', 'CONTENT_LENGTH', 'PHP_AUTH_USER', 'PHP_AUTH_PW', 'PHP_AUTH_DIGEST', 'AUTH_TYPE'];
                foreach ($server as $key => $value) {
                    $key = strtoupper($key);
                    if (strpos($key, 'HTTP_') === 0 || in_array($key, $specialHeaders)) {
                        if ($key === 'HTTP_CONTENT_TYPE' || $key === 'HTTP_CONTENT_LENGTH') {
                            continue;
                        }
                        $this->headers->set($key, $value);
                    } else {
                        $this->server->set($key, $value);
                    }
                }
        */

        // TODO : utiliser ce bout de code qui vient du framework FatFree ????
        /*
            if (function_exists('getallheaders')) {
                foreach (getallheaders() as $key=>$val) {
                    $tmp=strtoupper(strtr($key,'-','_'));
                    // TODO: use ucwords delimiters for php 5.4.32+ & 5.5.16+
                    $key=strtr(ucwords(strtolower(strtr($key,'-',' '))),' ','-');
                    $headers[$key]=$val;
                    if (isset($_SERVER['HTTP_'.$tmp]))
                        $headers[$key]=&$_SERVER['HTTP_'.$tmp];
                }
            }
            else {
                if (isset($_SERVER['CONTENT_LENGTH']))
                    $headers['Content-Length']=&$_SERVER['CONTENT_LENGTH'];
                if (isset($_SERVER['CONTENT_TYPE']))
                    $headers['Content-Type']=&$_SERVER['CONTENT_TYPE'];
                foreach (array_keys($_SERVER) as $key)
                    if (substr($key,0,5)=='HTTP_')
                        $headers[strtr(ucwords(strtolower(strtr(
                            substr($key,5),'_',' '))),' ','-')]=&$_SERVER[$key];
            }
*/

        $uri = $this->marshalUriFromServer($server);
        // TODO : ilm manque un normalizeFiles directement dans le constructeur !!!!! => $files   = static::normalizeFiles($_FILES);

        //die(print_r($uri));

        $method = $server['REQUEST_METHOD'];
        $protocol = isset($server['SERVER_PROTOCOL']) ? str_replace('HTTP/', '', $server['SERVER_PROTOCOL']) : '1.1';

        /*
                preg_match('/^HTTP\/([\d.]+)$/', $_SERVER['SERVER_PROTOCOL'], $match);
                $protocol = isset($match[1]) ? $protocol = $match[1] : '1.1';
        */

        //$body = 'php://input'; //new LazyOpenStream('php://input', 'r+');
        $body = StreamFactory::createFromStringOrResource('php://input', 'r+');

        $serverRequest = new ServerRequest($method, $uri, $headers, $body, $protocol, $server);

        // per PSR-7: attempt to set the Host header from a provided URI if no Host header is provided (ensure it's set as the first header)
        // TODO : Methode 1 ou Methode 2 ????
        /*
                if (! $this->hasHeader('Host') && $this->uri->getHost()) {
                    $this->headerNames['host'] = 'Host';
                    $this->headers['Host'] = [$this->getHostFromUri()];
                }
                // TODO : Methode 1 ou Methode 2 ????
                if (!$this->hasHeader('Host')) {
                    $this->updateHostFromUri();
                    // TODO : ca corrspond à renvoyer cela : https://github.com/zendframework/zend-diactoros/blob/master/src/RequestTrait.php#L309
                    //$host  = $this->uri->getHost();
                    //$host .= $this->uri->getPort() ? ':' . $this->uri->getPort() : '';
                }
        */

        return $serverRequest
            ->withCookieParams($_COOKIE)
            ->withQueryParams($_GET)
            ->withParsedBody($_POST)
            ->withUploadedFiles($this->normalizeFiles($_FILES)); // TODO : il manque un appel à normalizeFiles directement dans le constructeur !!!!! => $files   = static::normalizeFiles($files ?: $_FILES);
    }

    /**
     * Get a Uri populated with values from $_SERVER.
     *
     * @param array $server
     *
     * @return \Psr\Http\Message\UriInterface
     */
    public function marshalUriFromServer(array $server)
    {
        $uri = new Uri('');
        // TODO : vérifier si on a vraiment besoin d'alimenter le scheme. actuellement cela n'est pas fait dans les autres factories.
        //$uri = $uri->withScheme(! empty($server['HTTPS']) && $server['HTTPS'] !== 'off' ? 'https' : 'http');

        if (isset($server['REQUEST_SCHEME'])) {
            $uri = $uri->withScheme($server['REQUEST_SCHEME']);
        } elseif (isset($server['HTTPS'])) {
            $uri = $uri->withScheme('on' === $server['HTTPS'] ? 'https' : 'http');
        }

        $hasPort = false;
        if (isset($server['HTTP_HOST'])) {
            $hostHeaderParts = explode(':', $server['HTTP_HOST']);
            $uri = $uri->withHost($hostHeaderParts[0]);
            if (isset($hostHeaderParts[1])) {
                $hasPort = true;
                $uri = $uri->withPort($hostHeaderParts[1]);
            }
        } elseif (isset($server['SERVER_NAME'])) {
            $uri = $uri->withHost($server['SERVER_NAME']);
        } elseif (isset($server['SERVER_ADDR'])) {
            $uri = $uri->withHost($server['SERVER_ADDR']);
        }
        if (! $hasPort && isset($server['SERVER_PORT'])) {
            $uri = $uri->withPort($server['SERVER_PORT']);
        }
        $hasQuery = false;
        if (isset($server['REQUEST_URI'])) {
            $requestUriParts = explode('?', $server['REQUEST_URI'], 2);
            $uri = $uri->withPath($requestUriParts[0]);
            if (isset($requestUriParts[1])) {
                $hasQuery = true;
                $uri = $uri->withQuery($requestUriParts[1]);
            }
        }
        if (! $hasQuery && isset($server['QUERY_STRING'])) {
            $uri = $uri->withQuery($server['QUERY_STRING']);
        }

        return $uri;
    }

    /**
     * Marshal headers from $_SERVER.
     *
     * @param array $server
     *
     * @return array
     */
    public function marshalHeaders(array $server)
    {
        $headers = [];
        foreach ($server as $key => $value) {
            // Apache prefixes environment variables with REDIRECT_
            // if they are added by rewrite rules
            if (strpos($key, 'REDIRECT_') === 0) {
                $key = substr($key, 9);
                // We will not overwrite existing variables with the
                // prefixed versions, though
                if (array_key_exists($key, $server)) {
                    continue;
                }
            }
            if ($value && strpos($key, 'HTTP_') === 0) {
                $name = strtr(strtolower(substr($key, 5)), '_', '-');
                $headers[$name] = $value;

                continue;
            }
            if ($value && strpos($key, 'CONTENT_') === 0) {
                $name = 'content-' . strtolower(substr($key, 8));
                $headers[$name] = $value;

                continue;
            }
        }

        return $headers;
    }

    /**
     * Get all HTTP header key/values as an associative array for the current request.
     *
     * @param array $server
     *
     * Ported from symfony, see original:
     *
     * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Component/HttpFoundation/ServerBag.php#L28
     *
     * (c) Fabien Potencier <fabien@symfony.com>
     *
     * @return array
     */
    /*
    protected function getHeaders(array $server)
    {
        $headers        = [];
        $contentHeaders = [
            'CONTENT_LENGTH' => true,
            'CONTENT_MD5'    => true,
            'CONTENT_TYPE'   => true,
        ];
        foreach ($server as $key => $value) {
            if (\mb_strpos($key, 'HTTP_') === 0) {
                $headers[$key] = $value;
                // CONTENT_* are not prefixed with HTTP_
            } elseif (isset($contentHeaders[$key])) {
                $headers[$key] = $value;
            }
        }
        if (isset($server['PHP_AUTH_USER'])) {
            $headers['PHP_AUTH_USER'] = $server['PHP_AUTH_USER'];
            $headers['PHP_AUTH_PW']   = $server['PHP_AUTH_PW'] ?? '';
        } else {
            $authorizationHeader = null;
            if (isset($server['HTTP_AUTHORIZATION'])) {
                $authorizationHeader = $server['HTTP_AUTHORIZATION'];
            } elseif (isset($server['REDIRECT_HTTP_AUTHORIZATION'])) {
                $authorizationHeader = $server['REDIRECT_HTTP_AUTHORIZATION'];
            }
            if ($authorizationHeader !== null) {
                if (\mb_stripos($authorizationHeader, 'basic ') === 0) {
                    // Decode AUTHORIZATION header into PHP_AUTH_USER and PHP_AUTH_PW when authorization header is basic
                    $exploded = \explode(':', \base64_decode(\mb_substr($authorizationHeader, 6), true), 2);
                    if (\count($exploded) === 2) {
                        [$headers['PHP_AUTH_USER'], $headers['PHP_AUTH_PW']] = $exploded;
                    }
                } elseif (empty($server['PHP_AUTH_DIGEST']) && (0 === \mb_stripos($authorizationHeader, 'digest '))) {
                    // In some circumstances PHP_AUTH_DIGEST needs to be set
                    $headers['PHP_AUTH_DIGEST'] = $authorizationHeader;
                    $server['PHP_AUTH_DIGEST']  = $authorizationHeader;
                } elseif (\mb_stripos($authorizationHeader, 'bearer ') === 0) {
                    //
                    // XXX: Since there is no PHP_AUTH_BEARER in PHP predefined variables,
                    //      I'll just set $headers['AUTHORIZATION'] here.
                    //      http://php.net/manual/en/reserved.variables.server.php
                    //
                    $headers['HTTP_AUTHORIZATION'] = $authorizationHeader;
                }
            }
        }
        if (isset($headers['HTTP_AUTHORIZATION'])) {
            return $headers;
        }
        // PHP_AUTH_USER/PHP_AUTH_PW
        if (isset($headers['PHP_AUTH_USER'])) {
            $headers['HTTP_AUTHORIZATION'] = 'Basic ' . \base64_encode($headers['PHP_AUTH_USER'] . ':' . $headers['PHP_AUTH_PW']);
        } elseif (isset($headers['PHP_AUTH_DIGEST'])) {
            $headers['HTTP_AUTHORIZATION'] = $headers['PHP_AUTH_DIGEST'];
        }
        return $headers;
    }*/

    /**
     * Marshal the $_SERVER array.
     *
     * Pre-processes and returns the $_SERVER superglobal.
     *
     * @param array $server
     *
     * @return array
     */
    // fix : https://support.deskpro.com/en/kb/articles/missing-authorization-headers-with-apache
    private function normalizeServer(array $server)
    {
        // This seems to be the only way to get the Authorization header on Apache
        $apacheRequestHeaders = self::$apacheRequestHeaders;
        if (isset($server['HTTP_AUTHORIZATION']) || ! is_callable($apacheRequestHeaders)) {
            return $server;
        }
        //If HTTP_AUTHORIZATION does not exist tries to get it from "apache_request_headers()" when available.
        $headers = $apacheRequestHeaders();
        if (isset($headers['Authorization'])) {
            $server['HTTP_AUTHORIZATION'] = $headers['Authorization'];

            return $server;
        }
        if (isset($headers['authorization'])) {
            $server['HTTP_AUTHORIZATION'] = $headers['authorization'];

            return $server;
        }

        return $server;
    }

    /**
     * Return an UploadedFile instance array.
     *
     * @param array $files A array which respect $_FILES structure
     *
     * @throws InvalidArgumentException for unrecognized values
     *
     * @return array
     */
    public function normalizeFiles(array $files): array
    {
        $normalized = [];
        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized[$key] = $value;
            } elseif (is_array($value) && isset($value['tmp_name'])) {
                $normalized[$key] = $this->createUploadedFileFromSpec($value);
            } elseif (is_array($value)) {
                $normalized[$key] = $this->normalizeFiles($value);

                continue;
            } else {
                throw new InvalidArgumentException('Invalid value in files specification');
            }
        }

        return $normalized;
    }

    /**
     * Create and return an UploadedFile instance from a $_FILES specification.
     *
     * If the specification represents an array of values, this method will
     * delegate to normalizeNestedFileSpec() and return that return value.
     *
     * @param array $value $_FILES struct
     *
     * @return array|UploadedFileInterface
     */
    private function createUploadedFileFromSpec(array $value)
    {
        if (is_array($value['tmp_name'])) {
            return $this->normalizeNestedFileSpec($value);
        }

        return new UploadedFile(
            $value['tmp_name'],
            (int) $value['size'],
            (int) $value['error'],
            $value['name'],
            $value['type']
        );
    }

    /**
     * Normalize an array of file specifications.
     *
     * Loops through all nested files and returns a normalized array of
     * UploadedFileInterface instances.
     *
     * @param array $files
     *
     * @return UploadedFileInterface[]
     */
    private function normalizeNestedFileSpec(array $files = [])
    {
        $normalizedFiles = [];
        foreach (array_keys($files['tmp_name']) as $key) {
            $spec = [
                'tmp_name' => $files['tmp_name'][$key],
                'size'     => $files['size'][$key],
                'error'    => $files['error'][$key],
                'name'     => $files['name'][$key],
                'type'     => $files['type'][$key],
            ];
            $normalizedFiles[$key] = $this->createUploadedFileFromSpec($spec);
        }

        return $normalizedFiles;
    }

    /**
     * Create a new server request from a set of arrays.
     *
     * @param array $server  Typically $_SERVER or similar structure.
     * @param array $headers Typically the output of getallheaders() or similar structure.
     * @param array $cookie  Typically $_COOKIE or similar structure.
     * @param array $get     Typically $_GET or similar structure.
     * @param array $post    Typically $_POST or similar structure.
     * @param array $files   Typically $_FILES or similar structure.
     *
     * @throws InvalidArgumentException If no valid method or URI can be determined.
     *
     * @return ServerRequestInterface
     */
    public function createServerRequestFromArrays(
        array $server,
        array $headers,
        array $cookie,
        array $get,
        array $post,
        array $files
    ): ServerRequestInterface {
        $method = $this->getMethodFromEnvironment($server);
        $uri = $this->getUriFromEnvironmentWithHTTP($server);

        $protocol = isset($server['SERVER_PROTOCOL']) ? str_replace('HTTP/', '', $server['SERVER_PROTOCOL']) : '1.1';

        $serverRequest = new ServerRequest($method, $uri, $headers, null, $protocol, $server);

        return $serverRequest
            ->withCookieParams($cookie)
            ->withQueryParams($get)
            ->withParsedBody($post)
            ->withUploadedFiles(self::normalizeFiles($files));
    }

    private function getMethodFromEnvironment(array $environment): string
    {
        if (false === isset($environment['REQUEST_METHOD'])) {
            throw new InvalidArgumentException('Cannot determine HTTP method');
        }

        return $environment['REQUEST_METHOD'];
    }

    private function getUriFromEnvironmentWithHTTP(array $environment): \Psr\Http\Message\UriInterface
    {
        $uri = (new UriFactory())->createUriFromArray($environment);
        if ('' === $uri->getScheme()) {
            $uri = $uri->withScheme('http');
        }

        return $uri;
    }
}
