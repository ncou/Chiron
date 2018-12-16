<?php

declare(strict_types=1);

namespace Chiron\Routing\Strategy;

use Chiron\Routing\Resolver\CallableResolverInterface;
use Chiron\Http\Psr\Response;
use Chiron\Routing\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use InvalidArgumentException;

/**
 * Route callback strategy with route parameters as individual arguments and the response is encoded in json.
 */
class JsonStrategy extends AbstractStrategy
{
    /**
     * Default flags for json_encode.
     * Encode <, >, ', &, and " characters in the JSON, making it also safe to be embedded into HTML.
     * Doesn't encode the slash /.
     *
     * JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES
     */
    public const DEFAULT_JSON_FLAGS = 79;

    /**
     * @var int
     */
    private $encodingOptions = self::DEFAULT_JSON_FLAGS;

    /** CallableResolverInterface */
    private $resolver;
    /** ResponseFactoryInterface */
    private $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory, CallableResolverInterface $resolver)
    {
        $this->resolver = $resolver;
        $this->responseFactory = $responseFactory;
    }

    public function invokeRouteCallable(Route $route, ServerRequestInterface $request): ResponseInterface
    {
        $params = $route->getVars();
        // Inject individual matched parameters.
        foreach ($params as $param => $value) {
            $request = $request->withAttribute($param, $value);
        }

        $callable = $this->resolver->resolve($route->getHandler());
        $parameters = $this->bindParameters($request, $callable, $params);

        $response = $this->call($callable, $parameters);

        // TODO : lever une exception si le retour rencoyé par le controller n'est pas : JsonSerializableInterface ou ArrayObject ou is_array
        if (! $response instanceof ResponseInterface) {
            $json = $this->jsonEncode($response);

            // TODO : créer une méthode createResponse dans la classe abstraite avec comme signature : create($content = null, $status = 200, array $headers = [])
            $response = $this->responseFactory->createResponse(200);
            $response = $response->withHeader('Content-Type', 'application/json');
            $response->getBody()->write($json);
        }

        return $response;
    }

    /**
     * Encode the provided data to JSON.
     *
     * @param mixed $data
     *
     * @throws InvalidArgumentException if unable to encode the $data to JSON.
     *
     * @return string
     */
    public function jsonEncode($data): string
    {
        // TODO : attendre la version PHP 7.3 pour utiliser le flag JSON_THROW_ON_ERROR => https://wiki.php.net/rfc/json_throw_on_error
        $json = json_encode($data, $this->encodingOptions);

        if ($json === false) {
            throw new InvalidArgumentException(
                sprintf('Unable to encode data to JSON: %s', json_last_error_msg()),
                json_last_error());
        }

        return $json;
    }

    /**
     * Returns options used while encoding data to JSON.
     *
     * @return int
     */
    public function getEncodingOptions(): int
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
    public function setEncodingOptions(int $encodingOptions): self
    {
        $this->encodingOptions = $encodingOptions;

        return $this;
    }

    /**
     * Determine if the given content should be turned into JSON.
     *
     * @param  mixed  $content
     * @return bool
     */
    /*
    protected function shouldBeJson($content)
    {
        return $content instanceof ArrayObject ||
               $content instanceof JsonSerializable ||
               is_array($content);
    }*/

    /**
     * Check if the response can be converted to JSON
     *
     * Arrays can always be converted, objects can be converted if they're not a response already
     *
     * @param mixed $response
     *
     * @return bool
     */
    /*
    protected function isJsonEncodable($response) : bool
    {
        if ($response instanceof ResponseInterface) {
            return false;
        }
        return (is_array($response) || is_object($response));
    }*/
}
