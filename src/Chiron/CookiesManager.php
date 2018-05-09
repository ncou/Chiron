<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @link      https://github.com/slimphp/Slim
 * @copyright Copyright (c) 2011-2017 Josh Lockhart
 * @license   https://github.com/slimphp/Slim/blob/3.x/LICENSE.md (MIT License)
 */
namespace Chiron;

use InvalidArgumentException;
use DateTimeImmutable;

/**
 * Cookie helper
 */
class CookiesManager
{
    /**
     * Cookies for HTTP response
     *
     * @var array
     */
    protected $responseCookies = [];
    /**
     * Default cookie properties
     *
     * @var array
     */
    protected $defaults = [
        'value' => '',
        'domain' => null,
        'hostonly' => null,
        'path' => null,
        'expires' => null,
        'secure' => false,
        'httponly' => false,
        'samesite' => null
    ];
    /**
     * Set default cookie properties
     *
     * @param array $settings
     */
    public function setDefaults(array $settings)
    {
        $this->defaults = array_replace($this->defaults, $settings);
    }
    /**
     * Set response cookie
     *
     * @param string       $name  Cookie name
     * @param string|array $value Cookie value, or cookie properties
     */
    public function set($name, $value)
    {
        if (!is_array($value)) {
            $value = ['value' => (string)$value];
        }
        $this->responseCookies[$name] = array_replace($this->defaults, $value);
    }
    /**
     * Convert to `Set-Cookie` headers
     *
     * @return string[]
     */
    public function toHeaders()
    {
        $headers = [];
        foreach ($this->responseCookies as $name => $properties) {
            $headers[] = self::toHeader($name, $properties);
        }
        return $headers;
    }
    /**
     * Convert to `Set-Cookie` header
     *
     * @param  string $name       Cookie name
     * @param  array  $properties Cookie properties
     *
     * @return string
     */
    public static function toHeader($name, array $properties)
    {
        $result = rawurlencode($name) . '=' . rawurlencode($properties['value']);
        if (isset($properties['domain'])) {
            $result .= '; domain=' . $properties['domain'];
        }
        if (isset($properties['path'])) {
            $result .= '; path=' . $properties['path'];
        }
        if (isset($properties['expires'])) {
            if (is_string($properties['expires'])) {
                $timestamp = strtotime($properties['expires']);
            } else {
                $timestamp = (int)$properties['expires'];
            }
            if ($timestamp !== 0) {
                $result .= '; expires=' . gmdate('D, d-M-Y H:i:s e', $timestamp);
            }
        }
        if (isset($properties['secure']) && $properties['secure']) {
            $result .= '; secure';
        }
        if (isset($properties['hostonly']) && $properties['hostonly']) {
            $result .= '; HostOnly';
        }
        if (isset($properties['httponly']) && $properties['httponly']) {
            $result .= '; HttpOnly';
        }
        if (isset($properties['samesite']) && in_array(strtolower($properties['samesite']), ['lax', 'strict'], true)) {
            // While strtolower is needed for correct comparison, the RFC doesn't care about case
            $result .= '; SameSite=' . $properties['samesite'];
        }
        return $result;
    }


    /**
     * Parse Set-Cookie headers into array
     *
     * @param array $values List of Set-Cookie Header values.
     * @return array An array of cookies with all the settings
     */
    public static function parseSetCookieHeader(array $values): array
    {
        $cookies = [];
        foreach ($values as $value) {
            $value = rtrim($value, ';');
            $parts = preg_split('/\;[ \t]*/', $value);
            $name = false;
            // TODO : utiliser plutot le tableau des settings par défaut qui est défini en variable de classe
            $cookie = [
                'value' => '',
                'path' => '',
                'domain' => '',
                'secure' => false,
                'httponly' => false,
                'expires' => null,
                'max-age' => null
            ];
            foreach ($parts as $i => $part) {
                if (strpos($part, '=') !== false) {
                    list($key, $value) = explode('=', $part, 2);
                } else {
                    $key = $part;
                    $value = true;
                }
                if ($i === 0) {
                    $name = $key;
                    $cookie['value'] = urldecode($value);
                    continue;
                }
                $key = strtolower($key);
                if (array_key_exists($key, $cookie) && !strlen($cookie[$key])) {
                    $cookie[$key] = $value;
                }
            }
            $expires = null;
            if ($cookie['max-age'] !== null) {
                $expires = new DateTimeImmutable('@' . (time() + $cookie['max-age']));
            } elseif ($cookie['expires']) {
                $expires = new DateTimeImmutable('@' . strtotime($cookie['expires']));
            }

            $cookies[$name] = $cookie;
        }
        return $cookies;
    }
}
