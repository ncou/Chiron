<?php

declare(strict_types=1);

namespace Chiron\Http;

use Chiron\Container\SingletonInterface;
use Chiron\Facade\HttpDecorator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SplPriorityQueue;
use Chiron\Container\BindingInterface;
use Psr\Container\NotFoundExceptionInterface;
use Chiron\Exception\ApplicationException;
use Psr\Container\ContainerInterface;

// Méthode pour détecter le "base_path" de l'application.
//https://github.com/drupal/drupal/blob/9.0.x/core/lib/Drupal/Core/DrupalKernel.php#L1087



//https://github.com/spiral/http/blob/6d95493328f40dc71840119e006eaa42a443f82f/src/Request/InputManager.php


//https://github.com/symfony/symfony/blob/e60a876201b5b306d0c81a24d9a3db997192079c/src/Symfony/Component/HttpFoundation/UrlHelper.php

//https://github.com/symfony/symfony/blob/fb123e4fcafd684907ac2b72c293392dac0caa26/src/Symfony/Component/HttpFoundation/Request.php
//https://github.com/illuminate/http/blob/master/Request.php

//https://github.com/slimphp/Slim-Http/blob/master/src/ServerRequest.php


// PROXY :
//*********************
//https://symfony.com/doc/2.8/components/http_foundation/trusting_proxies.html
//https://github.com/symfony/symfony/blob/2.8/src/Symfony/Component/HttpFoundation/Request.php#L1948
//https://github.com/symfony/symfony/blob/e60a876201b5b306d0c81a24d9a3db997192079c/src/Symfony/Component/HttpFoundation/IpUtils.php#L37

//https://github.com/akrabat/proxy-detection-middleware/blob/master/src/ProxyDetection.php
//https://github.com/akrabat/ip-address-middleware/blob/master/src/IpAddress.php

//https://github.com/yiisoft/yii2/blob/65e56408104b702fe21eb1639dbaa6ffaa47900f/framework/web/Request.php#L211





// TODO : utiliser aussi une classe RouteContext qui serait retournée par une méthode getRouteContext() de cette classe ???? https://github.com/slimphp/Slim/blob/4.x/Slim/Routing/RouteContext.php
// TODO : ajouter un __call() pour permettre d'appeller l'ensemble des méthodes existantes dans la classe ServerRequestInterface.
// TODO : ajouter des méthodes pour récupérer l'ip le host et port + scheme derriére un proxy => ajouter a liste des ip pour les trustiesProxy + gérer les headers X-Forwarded
final class RequestContext implements SingletonInterface
{
    /** @var ServerRequestInterface */
    private $request = null;
    /** @var ContainerInterface */
    private $container = null;
    /** @var InputBag[] */
    private $bags = [];

    /**
     * @param ContainerInterface $container
     */
    // TODO : lui ajouter en paramétre une classe ProxiesConfig ou ProxyConfig ce qui permettra de récupérer les adresses IP ou plage d'ip qui sont "trusted" pour récupérer l'adresse ip et host/pot/scheme depuis les headers X-Forwarded-xxx, éventuellement au lieu de créer un fichier de config proxy.php.dist, on pourrait utiliser le fichier http.php.dist pour stocker ces infos, et utiliser un subset dans la classe de config pour récupérer que la partie proxy.
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Get active instance of ServerRequestInterface and reset all bags if instance changed.
     *
     * @return ServerRequestInterface
     *
     * @throws ApplicationException
     */
    // TODO : renommer la méthode en getRequest() ????
    public function request(): ServerRequestInterface
    {
        try {
            $request = $this->container->get(ServerRequestInterface::class);
        } catch (NotFoundExceptionInterface $e) {
            throw new ApplicationException(
                'Unable to get "ServerRequestInterface" in active container scope.',
                $e->getCode(),
                $e
            );
        }

        //Flushing input state
        if ($this->request !== $request) {
            $this->bags = [];
            $this->request = $request;
        }

        return $this->request;
    }


    /**
     * Gets the request's scheme.
     *
     * @return string
     */
    public function getScheme(): string
    {
        return $this->isSecure() ? 'https' : 'http';
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
    public function isSecure(): bool
    {
        /*
        if ($this->isFromTrustedProxy() && $proto = $this->getTrustedValues(self::HEADER_X_FORWARDED_PROTO)) {
            return \in_array(strtolower($proto[0]), ['https', 'on', 'ssl', '1'], true);
        }

        $https = $this->server->get('HTTPS');

        return !empty($https) && 'off' !== strtolower($https);
        */

        $https = $this->request()->getServerParams()['HTTPS'] ?? null;

        return !empty($https) && strtolower($https) !== 'off';
    }



    /**
     * Get the root URL for the application.
     *
     * @return string
     */
    public function root()
    {
        return rtrim($this->getSchemeAndHttpHost().$this->getBaseUrl(), '/');
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
        return $this->getScheme().'://'.$this->getHttpHost();
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
        $scheme = $this->getScheme();
        $port = $this->getPort();

        if (('http' == $scheme && 80 == $port) || ('https' == $scheme && 443 == $port)) {
            return $this->getHost();
        }

        return $this->getHost().':'.$port;
    }


    /**
     * Returns the host name.
     *
     * This method can read the client host name from the "X-Forwarded-Host" header
     * when trusted proxies were set via "setTrustedProxies()".
     *
     * The "X-Forwarded-Host" header must contain the client host name.
     *
     * @return string
     *
     * @throws SuspiciousOperationException when the host name is invalid or not trusted
     */
    public function getHost()
    {
        return $this->request()->getServerParams()['HTTP_HOST'];
    }

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
    public function getPort()
    {
        return $this->request()->getServerParams()['SERVER_PORT'];
    }




    /**
     * Prepares the base URL.
     *
     * @return string
     */
    // TODO : stocker le résultat du baseurl et le vider uniquement lors du flush dans la méthode du getRequest() quand la request à changé.
    public function getBaseUrl()
    {
        $server = $this->request()->getServerParams();



        $filename = basename($server['SCRIPT_FILENAME']);

        if (basename($server['SCRIPT_NAME']) === $filename) {
            $baseUrl = $server['SCRIPT_NAME'];
        } elseif (basename($server['PHP_SELF']) === $filename) {
            $baseUrl = $server['PHP_SELF'];
        } elseif (basename($server['ORIG_SCRIPT_NAME']) === $filename) {
            $baseUrl = $server['ORIG_SCRIPT_NAME']; // 1and1 shared hosting compatibility
        } else {
            // Backtrack up the script_filename to find the portion matching
            // php_self
            $path = $server['PHP_SELF'] ?? '';
            $file = $server['SCRIPT_FILENAME'] ?? '';
            $segs = explode('/', trim($file, '/'));
            $segs = array_reverse($segs);
            $index = 0;
            $last = \count($segs);
            $baseUrl = '';
            do {
                $seg = $segs[$index];
                $baseUrl = '/'.$seg.$baseUrl;
                ++$index;
            } while ($last > $index && (false !== $pos = strpos($path, $baseUrl)) && 0 != $pos);
        }

        // Does the baseUrl have anything in common with the request_uri?
        $requestUri = $this->getRequestUri();
        if ('' !== $requestUri && '/' !== $requestUri[0]) {
            $requestUri = '/'.$requestUri;
        }

        if ($baseUrl && null !== $prefix = $this->getUrlencodedPrefix($requestUri, $baseUrl)) {
            // full $baseUrl matches
            return $prefix;
        }

        if ($baseUrl && null !== $prefix = $this->getUrlencodedPrefix($requestUri, rtrim(\dirname($baseUrl), '/'.\DIRECTORY_SEPARATOR).'/')) {
            // directory portion of $baseUrl matches
            return rtrim($prefix, '/'.\DIRECTORY_SEPARATOR);
        }

        $truncatedRequestUri = $requestUri;
        if (false !== $pos = strpos($requestUri, '?')) {
            $truncatedRequestUri = substr($requestUri, 0, $pos);
        }

        $basename = basename($baseUrl);
        if (empty($basename) || !strpos(rawurldecode($truncatedRequestUri), $basename)) {
            // no match whatsoever; set it blank
            return '';
        }

        // If using mod_rewrite or ISAPI_Rewrite strip the script filename
        // out of baseUrl. $pos !== 0 makes sure it is not matching a value
        // from PATH_INFO or QUERY_STRING
        if (\strlen($requestUri) >= \strlen($baseUrl) && (false !== $pos = strpos($requestUri, $baseUrl)) && 0 !== $pos) {
            $baseUrl = substr($requestUri, 0, $pos + \strlen($baseUrl));
        }

        return rtrim($baseUrl, '/'.\DIRECTORY_SEPARATOR);
    }

    /*
     * The following methods are derived from code of the Zend Framework (1.10dev - 2010-01-24)
     *
     * Code subject to the new BSD license (https://framework.zend.com/license).
     *
     * Copyright (c) 2005-2010 Zend Technologies USA Inc. (https://www.zend.com/)
     */

    private function getRequestUri()
    {

        $server = $this->request()->getServerParams();


        $requestUri = '';

        if (isset($server['REQUEST_URI'])) {
            $requestUri = $server['REQUEST_URI'];

            if ('' !== $requestUri && '/' === $requestUri[0]) {
                // To only use path and query remove the fragment.
                if (false !== $pos = strpos($requestUri, '#')) {
                    $requestUri = substr($requestUri, 0, $pos);
                }
            } else {
                // HTTP proxy reqs setup request URI with scheme and host [and port] + the URL path,
                // only use URL path.
                $uriComponents = parse_url($requestUri);

                if (isset($uriComponents['path'])) {
                    $requestUri = $uriComponents['path'];
                }

                if (isset($uriComponents['query'])) {
                    $requestUri .= '?'.$uriComponents['query'];
                }
            }
        }

        return $requestUri;
    }

    /**
     * Returns the prefix as encoded in the string when the string starts with
     * the given prefix, null otherwise.
     */
    private function getUrlencodedPrefix(string $string, string $prefix): ?string
    {
        if (0 !== strpos(rawurldecode($string), $prefix)) {
            return null;
        }

        $len = \strlen($prefix);

        if (preg_match(sprintf('#^(%%[[:xdigit:]]{2}|.){%d}#', $len), $string, $match)) {
            return $match[0];
        }

        return null;
    }

}
