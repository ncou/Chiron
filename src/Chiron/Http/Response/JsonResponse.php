<?php

declare(strict_types=1);

namespace Chiron\Http\Response;

use Chiron\Http\Psr\Response;
use Chiron\Http\Psr\Stream;
use InvalidArgumentException;
use const JSON_ERROR_NONE;
use function is_object;
use function is_resource;
use function json_encode;
use function json_last_error;
use function json_last_error_msg;
use function sprintf;

/**
 * JSON response.
 *
 * Allows creating a response by passing data to the constructor; by default,
 * serializes the data to JSON, sets a status code of 200 and sets the
 * Content-Type header to application/json.
 */
class JsonResponse extends Response
{
    /**
     * Default flags for json_encode; value of:.
     *
     * <code>
     * JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES
     * </code>
     *
     * @const int
     */
    public const DEFAULT_JSON_FLAGS = 79;

    /**
     * @var mixed
     */
    private $payload;

    /**
     * @var int
     */
    private $encodingOptions;

    /**
     * Create a JSON response with the given data.
     *
     * Default JSON encoding is performed with the following options, which
     * produces RFC4627-compliant JSON, capable of embedding into HTML.
     *
     * - JSON_HEX_TAG
     * - JSON_HEX_APOS
     * - JSON_HEX_AMP
     * - JSON_HEX_QUOT
     * - JSON_UNESCAPED_SLASHES
     *
     * @param mixed $data            Data to convert to JSON.
     * @param int   $status          Integer status code for the response; 200 by default.
     * @param array $headers         Array of headers to use at initialization.
     * @param int   $encodingOptions JSON encoding options to use.
     *
     * @throws InvalidArgumentException if unable to encode the $data to JSON.
     */
    public function __construct(
        $data,
        int $status = 200,
        array $headers = [],
        $encodingOptions = self::DEFAULT_JSON_FLAGS
    ) {
        $this->setPayload($data);
        $this->encodingOptions = $encodingOptions;
        $json = $this->jsonEncode($data, $this->encodingOptions);
        $body = $this->createBodyFromJson($json);
        $headers = $this->injectContentType('application/json', $headers);
        parent::__construct($status, $headers, $body);
    }

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param $data
     *
     * @return JsonResponse
     */
    public function withPayload($data)
    {
        $new = clone $this;
        $new->setPayload($data);

        return $this->updateBodyFor($new);
    }

    /**
     * @return int
     */
    public function getEncodingOptions()
    {
        return $this->encodingOptions;
    }

    /**
     * @param int $encodingOptions
     *
     * @return JsonResponse
     */
    public function withEncodingOptions($encodingOptions)
    {
        $new = clone $this;
        $new->encodingOptions = $encodingOptions;

        return $this->updateBodyFor($new);
    }

    /**
     * @param string $json
     *
     * @return Stream
     */
    private function createBodyFromJson($json)
    {
        $body = new Stream(fopen('php://temp', 'wb+'));
        $body->write($json);
        $body->rewind();

        return $body;
    }

    /**
     * Encode the provided data to JSON.
     *
     * @param mixed $data
     * @param int   $encodingOptions
     *
     * @throws InvalidArgumentException if unable to encode the $data to JSON.
     *
     * @return string
     */
    private function jsonEncode($data, $encodingOptions)
    {
        if (is_resource($data)) {
            throw new InvalidArgumentException('Cannot JSON encode resources');
        }
        // Clear json_last_error()
        json_encode(null);
        $json = json_encode($data, $encodingOptions);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException(sprintf(
                'Unable to encode data to JSON in %s: %s',
                __CLASS__,
                json_last_error_msg()
            ));
        }

        return $json;
    }

    /**
     * @param $data
     */
    private function setPayload($data)
    {
        if (is_object($data)) {
            $data = clone $data;
        }
        $this->payload = $data;
    }

    /**
     * Update the response body for the given instance.
     *
     * @param self $toUpdate Instance to update.
     *
     * @return JsonResponse Returns a new instance with an updated body.
     */
    private function updateBodyFor(self $toUpdate)
    {
        $json = $this->jsonEncode($toUpdate->payload, $toUpdate->encodingOptions);
        $body = $this->createBodyFromJson($json);

        return $toUpdate->withBody($body);
    }

    /**
     * Inject the provided Content-Type, if none is already present.
     *
     * @param string $contentType
     * @param array  $headers
     *
     * @return array Headers with injected Content-Type
     */
    // TODO : à virer !!!!
    private function injectContentType($contentType, array $headers)
    {
        $hasContentType = array_reduce(array_keys($headers), function ($carry, $item) {
            return $carry ?: (strtolower($item) === 'content-type');
        }, false);
        if (! $hasContentType) {
            $headers['content-type'] = [$contentType];
        }

        return $headers;
    }

    // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    // SYMFONY RESPONSE :
    // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

    protected $data;

    protected $callback;

    // Encode <, >, ', &, and " characters in the JSON, making it also safe to be embedded into HTML.
    // 15 === JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    public const DEFAULT_ENCODING_OPTIONS = 15;

//    protected $encodingOptions = self::DEFAULT_ENCODING_OPTIONS;
    /**
     * @param mixed $data    The response data
     * @param int   $status  The response status code
     * @param array $headers An array of response headers
     * @param bool  $json    If the data is already a JSON string
     */
    /*
    public function __construct($data = null, int $status = 200, array $headers = array(), bool $json = false)
    {
        parent::__construct('', $status, $headers);
        if (null === $data) {
            $data = new \ArrayObject();
        }
        $json ? $this->setJson($data) : $this->setData($data);
    }*/

    /**
     * Factory method for chainability.
     *
     * Example:
     *
     *     return JsonResponse::create($data, 200)
     *         ->setSharedMaxAge(300);
     *
     * @param mixed $data    The json response data
     * @param int   $status  The response status code
     * @param array $headers An array of response headers
     *
     * @return static
     */
    public static function create($data = null, $status = 200, $headers = [])
    {
        return new static($data, $status, $headers);
    }

    /**
     * Make easier the creation of JsonResponse from raw json.
     */
    public static function fromJsonString($data = null, $status = 200, $headers = [])
    {
        return new static($data, $status, $headers, true);
    }

    /**
     * Sets the JSONP callback.
     *
     * @param string|null $callback The JSONP callback or null to use none
     *
     * @throws \InvalidArgumentException When the callback name is not valid
     *
     * @return $this
     */
    public function setCallback($callback = null)
    {
        if (null !== $callback) {
            // partially taken from http://www.geekality.net/2011/08/03/valid-javascript-identifier/
            // partially taken from https://github.com/willdurand/JsonpCallbackValidator
            //      JsonpCallbackValidator is released under the MIT License. See https://github.com/willdurand/JsonpCallbackValidator/blob/v1.1.0/LICENSE for details.
            //      (c) William Durand <william.durand1@gmail.com>

//            $pattern = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*(?:\[(?:"(?:\\\.|[^"\\\])*"|\'(?:\\\.|[^\'\\\])*\'|\d+)\])*?$/u';

            $reserved = [
                'break', 'do', 'instanceof', 'typeof', 'case', 'else', 'new', 'var', 'catch', 'finally', 'return', 'void', 'continue', 'for', 'switch', 'while',
                'debugger', 'function', 'this', 'with', 'default', 'if', 'throw', 'delete', 'in', 'try', 'class', 'enum', 'extends', 'super',  'const', 'export',
                'import', 'implements', 'let', 'private', 'public', 'yield', 'interface', 'package', 'protected', 'static', 'null', 'true', 'false',
            ];
            $parts = explode('.', $callback);
            foreach ($parts as $part) {
                if (! preg_match($pattern, $part) || in_array($part, $reserved, true)) {
                    throw new \InvalidArgumentException('The callback name is not valid.');
                }
            }
        }
        $this->callback = $callback;

        return $this->update();
    }

    /**
     * Sets a raw string containing a JSON document to be sent.
     *
     * @param string $json
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setJson($json)
    {
        $this->data = $json;

        return $this->update();
    }

    /**
     * Sets the data to be sent as JSON.
     *
     * @param mixed $data
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setData($data = [])
    {
        try {
            $data = json_encode($data, $this->encodingOptions);
        } catch (\Exception $e) {
            if ('Exception' === get_class($e) && 0 === strpos($e->getMessage(), 'Failed calling ')) {
                throw $e->getPrevious() ?: $e;
            }

            throw $e;
        }
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException(json_last_error_msg());
        }

        return $this->setJson($data);
    }

    /**
     * Returns options used while encoding data to JSON.
     *
     * @return int
     */
    public function getEncodingOptions2()
    {
        return $this->encodingOptions;
    }

    /**
     * Sets options used while encoding data to JSON.
     *
     * @param int $encodingOptions
     *
     * @return $this
     */
    public function setEncodingOptions2($encodingOptions)
    {
        $this->encodingOptions = (int) $encodingOptions;

        return $this->setData(json_decode($this->data));
    }

    /**
     * Updates the content and headers according to the JSON data and callback.
     *
     * @return $this
     */
    protected function update()
    {
        if (null !== $this->callback) {
            // Not using application/javascript for compatibility reasons with older browsers.
            $this->headers->set('Content-Type', 'text/javascript');

            return $this->setContent(sprintf('/**/%s(%s);', $this->callback, $this->data));
        }
        // Only set the header when there is none or when it equals 'text/javascript' (from a previous update with callback)
        // in order to not overwrite a custom definition.
        if (! $this->headers->has('Content-Type') || 'text/javascript' === $this->headers->get('Content-Type')) {
            $this->headers->set('Content-Type', 'application/json');
        }

        return $this->setContent($this->data);
    }

    /*
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
    /*
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
    }*/
}
