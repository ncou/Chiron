<?php

declare(strict_types=1);

namespace Chiron\Routing\Strategy;

use Chiron\Http\Psr\Response;
use Chiron\Routing\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use InvalidArgumentException;

/**
 * Route callback strategy with route parameters as individual arguments.
 */
// TODO : à renommer en JsonStrategy
class JsonInvocationStrategy extends AbstractStrategy
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

    // TODO : passer en paramétre un PSR17 Factory pour créer l'objet PSR7 Response
    public function __construct(CallableResolverInterface $resolver)
    {
        $this->resolver = $resolver;
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

        $json = $this->jsonEncode($response);

        //$response = $this->responseFactory->createResponse(200);
        $response = new Response(200);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write($json);

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
}
