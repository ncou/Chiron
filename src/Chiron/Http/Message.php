<?php

declare(strict_types=1);

namespace Chiron\Http;

// TODO : example : https://github.com/narrowspark/framework/blob/master/src/Viserio/Component/Http/AbstractMessage.php

// TODO : regarder les interfaces ici pour voir si la classe est PSR7 compatible https://github.com/php-fig/http-message/tree/master/src

use Psr\Http\Message\StreamInterface;

class Message
{
    /**
     * EOL characters used for HTTP message.
     *
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec2.html#sec2.2
     *
     * @var string
     */
    public const EOL = "\r\n";

    /**
     * A map of valid protocol versions.
     *
     * @var array
     */

    // TODO : utiliser plutot cette méthode : https://github.com/zendframework/zend-diactoros/blob/master/src/MessageTrait.php#L341
    protected static $validProtocolVersions = [
        '1.0' => true,
        '1.1' => true,
        '2.0' => true,
        '2'   => true,
    ];

    protected $protocol = '1.1';

    /** @var array Map of all registered headers, as original name => array of values */
    protected $headers = [];
    /** @var array Map of lowercase header name => original name at registration */
    protected $headerNames = [];

    /**
     * @var StreamInterface
     */
    protected $stream;

    /**
     * Disable magic setter to ensure immutability.
     */
    //https://github.com/slimphp/Slim-Http/blob/master/src/Message.php#L64
    /*
    public function __set($name, $value)
    {
        // Do nothing
    }
    */

    /*******************************************************************************
     * Protocol
     ******************************************************************************/

    public function getProtocolVersion()
    {
        return $this->protocol;
    }

    public function withProtocolVersion($version)
    {

      // TODO : vérifier l'utilité de conserver ce contrôle !!!!!!!! et donc le tableau de constantes correspondant en début de classe.
        // TODO : utiliser plutot ce controle : https://github.com/zendframework/zend-diactoros/blob/fb7f06e1b78c2aa17d08f30633bb2fa337428182/src/MessageTrait.php#L357
        if (!isset(self::$validProtocolVersions[$version])) {
            throw new InvalidArgumentException('Invalid HTTP version. Must be one of: ' . implode(', ', array_keys(self::$validProtocolVersions)));
        }

        /*
              preg_match('~^HTTP/([1-9]\.[0-9])$~', $request->server->get('SERVER_PROTOCOL'), $versionMatches);
              if ($versionMatches) {
                $this->setProtocolVersion($versionMatches[1]);
              }
        */

        $new = clone $this;
        $new->protocol = $version;

        return $new;
    }

    /**
     * The HTTP protocol version used.
     *
     * @var string|null
     */
//       protected $protocol;

    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * @return string HTTP protocol version.
     */
    /*
      public function getProtocolVersion()
      {
     if ($this->protocol) {
         return $this->protocol;
     }

     // Lazily populate this data as it is generally not used.
     preg_match('/^HTTP\/([\d.]+)$/', $this->getEnv('SERVER_PROTOCOL'), $match);
     $protocol = '1.1';
     if (isset($match[1])) {
         $protocol = $match[1];
     }
     $this->protocol = $protocol;

     return $this->protocol;
      }
      */

    /**
     * Return an instance with the specified HTTP protocol version.
     *
     * The version string MUST contain only the HTTP version number (e.g.,
     * "1.1", "1.0").
     *
     * @param string $version HTTP protocol version
     *
     * @return static
     */
    /*
    public function withProtocolVersion($version)
    {
        if (!preg_match('/^(1\.[01]|2)$/', $version)) {
            throw new InvalidArgumentException("Unsupported protocol version '{$version}' provided");
        }
        $new = clone $this;
        $new->protocol = $version;

        return $new;
    }
    */

    /*******************************************************************************
     * Headers
     ******************************************************************************/

    /**
     * Retrieves all message headers.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * @return array Returns an associative array of the message's headers. Each
     *               key MUST be a header name, and each value MUST be an array of strings.
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    public function hasHeader($header)
    {
        return isset($this->headerNames[strtolower($header)]);
    }

    public function getHeader($header)
    {
        $header = strtolower($header);
        if (!isset($this->headerNames[$header])) {
            return [];
        }
        $header = $this->headerNames[$header];

        return $this->headers[$header];
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * This method returns all of the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma.
     *
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeader() instead
     * and supply your own delimiter when concatenating.
     *
     * If the header does not appear in the message, this method MUST return
     * an empty string.
     *
     * @param string $header Case-insensitive header field name.
     *
     * @return string A string of values as provided for the given header
     *                concatenated together using a comma. If the header does not appear in
     *                the message, this method MUST return an empty string.
     */
    public function getHeaderLine($header)
    {
        return implode(', ', $this->getHeader($header));
    }

    /**
     * Return an instance with the provided value replacing the specified header.
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new and/or updated header and value.
     *
     * @param string          $header Case-insensitive header field name.
     * @param string|string[] $value  Header value(s).
     *
     * @throws \InvalidArgumentException for invalid header names or values.
     *
     * @return static
     */
    public function withHeader($header, $value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }
        $value = $this->trimHeaderValues($value);
        $normalized = strtolower($header);
        $new = clone $this;
        if (isset($new->headerNames[$normalized])) {
            unset($new->headers[$new->headerNames[$normalized]]);
        }
        $new->headerNames[$normalized] = $header;
        $new->headers[$header] = $value;

        return $new;
    }

    /**
     * Return an instance with the specified header appended with the given value.
     *
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new header and/or value.
     *
     * @param string $header Case-insensitive header field name to add.
     * @param string $value  Header value.
     *
     * @throws \InvalidArgumentException for invalid header names or values.
     *
     * @return static
     */
    public function withAddedHeader($header, $value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }
        $value = $this->trimHeaderValues($value);
        $normalized = strtolower($header);
        $new = clone $this;
        if (isset($new->headerNames[$normalized])) {
            $header = $this->headerNames[$normalized];
            $new->headers[$header] = array_merge($this->headers[$header], $value);
        } else {
            $new->headerNames[$normalized] = $header;
            $new->headers[$header] = $value;
        }

        return $new;
    }

    /**
     * Return an instance without the specified header.
     *
     * Header resolution MUST be done without case-sensitivity.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the named header.
     *
     * @param string $header Case-insensitive header field name to remove.
     *
     * @return static
     */
    public function withoutHeader($header)
    {
        $normalized = strtolower($header);
        if (!isset($this->headerNames[$normalized])) {
            return $this;
        }
        $header = $this->headerNames[$normalized];
        $new = clone $this;
        unset($new->headers[$header], $new->headerNames[$normalized]);

        return $new;
    }

    // TODO : Méthode utilisé depuis la partie Request, il faudrait voir si en utilisant la méthode withHeader ou withAddedHeader dans une boucle on peut se passer de cette méthode !!!!
    // TODO : https://github.com/guzzle/psr7/blob/master/src/MessageTrait.php#L143
    // TODO : https://github.com/zendframework/zend-diactoros/blob/master/src/MessageTrait.php#L318

    /**
     * Filter a set of headers to ensure they are in the correct internal format.
     *
     * Used by message constructors to allow setting all initial headers at once.
     *
     * @param array $originalHeaders Headers to filter.
     */
    protected function setHeaders(array $headers)
    {
        $this->headerNames = $this->headers = [];
        foreach ($headers as $header => $value) {
            if (!is_array($value)) {
                $value = [$value];
            }
            $value = $this->trimHeaderValues($value);
            $normalized = strtolower($header);
            if (isset($this->headerNames[$normalized])) {
                $header = $this->headerNames[$normalized];
                $this->headers[$header] = array_merge($this->headers[$header], $value);
            } else {
                $this->headerNames[$normalized] = $header;
                $this->headers[$header] = $value;
            }
        }
    }

    /**
     * @param array $headers
     *                       'key' => 'value'
     */
    //https://github.com/freedompy/phpgear/blob/master/src/Psr7/PSRMessage.php#L23
    /*
    public function setHeaders(array &$headers)
    {
        $this->headers = array_map(function ($val) {
            return [trim($val, " \t")];
        }, $headers);
    }
    */

    /*******************************************************************************
     * Body
     ******************************************************************************/

    public function withBody(StreamInterface $body)
    {
        $new = clone $this;
        $new->stream = $body;

        return $new;
    }

    public function getBody()
    {
        return $this->stream;
    }

    /*******************************************************************************
     * Helpers
     ******************************************************************************/

    /**
     * Trims whitespace from the header values.
     *
     * Spaces and tabs ought to be excluded by parsers when extracting the field value from a header field.
     *
     * header-field = field-name ":" OWS field-value OWS
     * OWS          = *( SP / HTAB )
     *
     * @param string[] $values Header values
     *
     * @return string[] Trimmed header values
     *
     * @see https://tools.ietf.org/html/rfc7230#section-3.2.4
     */
    private function trimHeaderValues(array $values)
    {
        return array_map(function ($value) {
            return is_string($value) ? trim($value, " \t") : $value;
        }, $values);
    }

    /**
     * Normalize header name.
     *
     * This method transforms header names into a
     * normalized form. This is how we enable case-insensitive
     * header names in the other methods in this class.
     *
     * @param string $key The case-insensitive header name
     *
     * @return string Normalized header name
     */
    /*
    private function normalizeHeaderName($name)
    {
      //return strtr(strtolower($name), '_', '-');

      $name = strtr(strtolower($name), '_', '-');
      if (strpos($name, 'http-') === 0) {
          $name = substr($name, 5);
      }

      return $name;
    }*/

    /**
     * Normalize a header name into the SERVER version.
     *
     * @param string $name The header name.
     *
     * @return string The normalized header name.
     */
    //https://github.com/cakephp/cakephp/blob/master/src/Http/ServerRequest.php#L955
    /*
    protected function normalizeHeaderName($name)
    {
        $name = str_replace('-', '_', strtoupper($name));
        if (!in_array($name, ['CONTENT_LENGTH', 'CONTENT_TYPE'])) {
            $name = 'HTTP_' . $name;
        }
        return $name;
    }
*/

    protected function getStream($stream, $modeIfNotInstance)
    {
        if ($stream instanceof StreamInterface) {
            return $stream;
        }
        if (!is_string($stream) && !is_resource($stream)) {
            throw new InvalidArgumentException(
                'Stream must be a string stream resource identifier, '
                . 'an actual stream resource, '
                . 'or a Psr\Http\Message\StreamInterface implementation'
            );
        }

        return new Stream($stream, $modeIfNotInstance);
    }
}
