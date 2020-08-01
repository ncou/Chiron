<?php

declare(strict_types=1);

namespace Chiron\Http\Helper;

use InvalidArgumentException;
use const JSON_ERROR_NONE;
use JsonSerializable;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;

//https://github.com/laravel/framework/blob/7.x/src/Illuminate/Routing/ResponseFactory.php

final class ResponseCreator
{
    /** @var ResponseFactoryInterface */
    private $responseFactory;

    /** @var StreamFactoryInterface */
    private $streamFactory;

    /**
     * @param ResponseFactoryInterface $responseFactory
     * @param StreamFactoryInterface   $streamFactory
     */
    public function __construct(ResponseFactoryInterface $responseFactory, StreamFactoryInterface $streamFactory)
    {
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
    }

    /**
     * Create a new response.
     *
     * @param int    $code
     * @param string $reasonPhrase
     *
     * @return ResponseInterface
     */
    public function create(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return $this->responseFactory->createResponse($code, $reasonPhrase);
    }

    /**
     * Write html content into response and set content-type header.
     *
     * @param string $html
     * @param int    $code
     * @param string $contentType
     *
     * @return ResponseInterface
     */
    public function html(
        string $html,
        int $code = 200,
        string $contentType = 'text/html'
    ): ResponseInterface {
        $response = $this->create($code);
        $response->getBody()->write($html);

        return $response->withHeader('Content-Type', $contentType);
    }

    /**
     * Mount redirect headers into response.
     *
     * @param UriInterface|string $uri
     * @param int                 $code
     *
     * @throws ResponseException
     *
     * @return ResponseInterface
     */
    // TODO : à utiliser dans le cadre du RedirectController utilisé par le RouteCollector ????
    public function redirect($uri, int $code = 302): ResponseInterface
    {
        if (! is_string($uri) && ! $uri instanceof UriInterface) {
            throw new InvalidArgumentException('Redirect allowed only for string or UriInterface uris');
        }

        return $this->create($code)->withHeader('Location', (string) $uri);
    }

    /**
     * Write json data into response and set content-type header.
     *
     * @param mixed $data
     * @param int   $code
     *
     * @return ResponseInterface
     */
    //https://github.com/laravel/framework/blob/7.x/src/Illuminate/Http/JsonResponse.php
    //https://github.com/symfony/http-foundation/blob/master/JsonResponse.php
    public function json(
        $data,
        int $code = 200,
        string $contentType = 'application/json'
    ): ResponseInterface {
        if ($data instanceof JsonSerializable) {
            $data = $data->jsonSerialize();
        }

        // 'status' key is used in the "api problem" specifications
        if (is_array($data) && isset($data['status'])) {
            $code = $data['status'];
        }

        // TODO : améliorer le flag utilisé pour le json : https://github.com/symfony/http-foundation/blob/master/JsonResponse.php#L32
        $json = (string) json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(json_last_error_msg(), json_last_error());
        }

        $response = $this->create($code);
        $response->getBody()->write($json);

        return $response->withHeader('Content-Type', $contentType);
    }

    /**
     * This method will trigger the client to download the specified file
     * It will append the `Content-Disposition` header to the response object.
     *
     * @param string|resource|StreamInterface $file
     * @param string|null                     $name
     * @param bool|string                     $contentType
     *
     * @return static
     */
    public function attachment($file, ?string $name = null, $contentType = true): ResponseInterface
    {
        $disposition = 'attachment';
        $fileName = $name;

        if (is_string($file) && $name === null) {
            $fileName = basename($file);
        }

        if ($name === null && (is_resource($file) || $file instanceof StreamInterface)) {
            $metaData = $file instanceof StreamInterface
                ? $file->getMetadata()
                : stream_get_meta_data($file);

            if (is_array($metaData) && isset($metaData['uri'])) {
                $uri = $metaData['uri'];
                if ('php://' !== substr($uri, 0, 6)) {
                    $fileName = basename($uri);
                }
            }
        }

        if (is_string($fileName) && strlen($fileName)) {
            /*
             * The regex used below is to ensure that the $fileName contains only
             * characters ranging from ASCII 128-255 and ASCII 0-31 and 127 are replaced with an empty string
             */
            $disposition .= '; filename="' . preg_replace('/[\x00-\x1F\x7F\"]/', ' ', $fileName) . '"';
            $disposition .= "; filename*=UTF-8''" . rawurlencode($fileName);
        }

        return $this
            ->file($file, $contentType)
            ->withHeader('Content-Disposition', $disposition);
    }

    /**
     * This method prepares the response object to return a file response to the
     * client without `Content-Disposition` header which defaults to `inline`.
     *
     * You control the behavior of the `Content-Type` header declaration via `$contentType`
     * Use a string to override the header to a value of your choice. e.g.: `application/json`
     * When set to `true` we attempt to detect the content type using `mime_content_type`
     * When set to `false` the content type is not added to the headers.
     *
     * @param string|resource|StreamInterface $file
     * @param bool|string                     $contentType
     *
     * @throws RuntimeException         If the file cannot be opened.
     * @throws InvalidArgumentException If the mode is invalid.
     *
     * @return static
     */
    public function file($file, $contentType = true): ResponseInterface
    {
        $response = $this->create();

        if (is_resource($file)) {
            $response = $response->withBody($this->streamFactory->createStreamFromResource($file));
        } elseif (is_string($file)) {
            // TODO : il faudrait vérifier que le fichier existe bien via la méthode is_file et si ce n''est pas le cas lever une exception : throw new InvalidArgumentException('Unable to allocate response body stream, file does not exist'); car sinon je pense que la méthode createStreamFromFile va péter !!!!
            $response = $response->withBody($this->streamFactory->createStreamFromFile($file));
        } elseif ($file instanceof StreamInterface) {
            $response = $response->withBody($file);
        } else {
            throw new InvalidArgumentException(
                'Parameter $file must be a resource, a string or an instance of Psr\Http\Message\StreamInterface.'
            );
        }

        if ($contentType === true) {
            $contentType = is_string($file) ? mime_content_type($file) : 'application/octet-stream';
        }

        if (is_string($contentType)) {
            $response = $response->withHeader('Content-Type', $contentType);
        }

        return $response;
    }
}
