<?php

declare(strict_types=1);

namespace Chiron\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ClientIpMiddleware implements MiddlewareInterface
{
    /**
     * @var bool
     */
    private $remote = false;

    /**
     * @var string The attribute name
     */
    private $attribute = 'client-ip';

    /**
     * @var array The trusted proxy headers
     */
    private $proxyHeaders = [];

    /**
     * @var array The trusted proxy ips
     */
    private $proxyIps = [];

    /**
     * Configure the proxy.
     */
    public function proxy(
        array $ips = [],
        array $headers = [
            'Forwarded',
            'Forwarded-For',
            'X-Forwarded',
            'X-Forwarded-For',
            'X-Cluster-Client-Ip',
            'Client-Ip',
        ]
    ) {
        $this->proxyIps = $ips;
        $this->proxyHeaders = $headers;

        return $this;
    }

    /**
     * To get the ip from a remote service.
     * Useful for testing purposes on localhost.
     */
    public function remote(bool $remote = true): self
    {
        $this->remote = $remote;

        return $this;
    }

    /**
     * Set the attribute name to store client's IP address.
     */
    public function attribute(string $attribute): self
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Process a server request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $ip = $this->getIp($request);

        return $handler->handle($request->withAttribute($this->attribute, $ip));
    }

    /**
     * Detect and return the ip.
     *
     * @return string|null
     */
    private function getIp(ServerRequestInterface $request)
    {
        $remoteIp = $this->getRemoteIp();
        if (! empty($remoteIp)) {
            // Found IP address via remote service.
            return $remoteIp;
        }
        $localIp = $this->getLocalIp($request);
        if ($this->proxyIps && ! in_array($localIp, $this->proxyIps)) {
            // Local IP address does not point at a known proxy, do not attempt to read proxied IP address.
            return $localIp;
        }
        $proxiedIp = $this->getProxiedIp($request);
        if (! empty($proxiedIp)) {
            // Found IP address via proxy-defined headers.
            return $proxiedIp;
        }

        return $localIp;
    }

    /**
     * Returns the IP address from remote service.
     *
     * @return string|null
     */
    // TODO : c'est à virer !!!!!
    private function getRemoteIp()
    {
        if ($this->remote) {
            $ip = file_get_contents('http://ipecho.net/plain');
            if (self::isValid($ip)) {
                return $ip;
            }
        }
    }

    /**
     * Returns the first valid proxied IP found.
     *
     * @return string|null
     */
    private function getProxiedIp(ServerRequestInterface $request)
    {
        foreach ($this->proxyHeaders as $name) {
            if ($request->hasHeader($name)) {
                $ip = self::getHeaderIp($request->getHeaderLine($name));
                if ($ip !== null) {
                    return $ip;
                }
            }
        }
    }

    /**
     * Returns the remote address of the request, if valid.
     *
     * @return string|null
     */
    private function getLocalIp(ServerRequestInterface $request)
    {
        $server = $request->getServerParams();
        if (! empty($server['REMOTE_ADDR']) && self::isValid($server['REMOTE_ADDR'])) {
            return $server['REMOTE_ADDR'];
        }
    }

    /**
     * Returns the first valid ip found in the header.
     *
     * @return string|null
     */
    private static function getHeaderIp(string $header)
    {
        foreach (array_map('trim', explode(',', $header)) as $ip) {
            if (self::isValid($ip)) {
                return $ip;
            }
        }
    }

    /**
     * Check that a given string is a valid IP address.
     */
    private static function isValid(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6) !== false;
    }

    // TODO : voir si on peut réutiliser cette fonction !!!!
    //https://github.com/PrestaShop/PrestaShop/blob/ce881a124f8e6e2900396564f2c67f6dd4ebd65d/classes/Tools.php#L333

    /**
     * Get the server variable REMOTE_ADDR, or the first ip of HTTP_X_FORWARDED_FOR (when using proxy).
     *
     * @return string $remote_addr ip of client
     */
    public static function getRemoteAddr()
    {
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
        } else {
            $headers = $_SERVER;
        }
        if (array_key_exists('X-Forwarded-For', $headers)) {
            $_SERVER['HTTP_X_FORWARDED_FOR'] = $headers['X-Forwarded-For'];
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && (! isset($_SERVER['REMOTE_ADDR'])
            || preg_match('/^127\..*/i', trim($_SERVER['REMOTE_ADDR'])) || preg_match('/^172\.16.*/i', trim($_SERVER['REMOTE_ADDR']))
            || preg_match('/^192\.168\.*/i', trim($_SERVER['REMOTE_ADDR'])) || preg_match('/^10\..*/i', trim($_SERVER['REMOTE_ADDR'])))) {
            if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',')) {
                $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

                return $ips[0];
            } else {
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    /*
     * Gets the client IP.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request used.
     * @return string IP of the client.
     */
    /*
    protected function getClientIp($request)
    {
        if (method_exists($request, 'clientIp')) {
            return $request->clientIp();
        }
        // @codeCoverageIgnoreStart
        if ($request instanceof ServerRequestInterface) {
            $ip = '';
            $serverParams = $request->getServerParams();
            if (!empty($serverParams['HTTP_CLIENT_IP'])) {
                $ip = $serverParams['HTTP_CLIENT_IP'];
            } elseif (!empty($serverParams['HTTP_X_FORWARDED_FOR'])) {
                $ip = $serverParams['HTTP_X_FORWARDED_FOR'];
            } elseif (!empty($serverParams['REMOTE_ADDR'])) {
                $ip = $serverParams['REMOTE_ADDR'];
            }
            return $ip;
        }
        // @codeCoverageIgnoreEnd
        return '';
    }*/
}
