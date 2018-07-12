<?php

declare(strict_types=1);

namespace Chiron\Http\Middleware;

use Chiron\Http\Psr\Response;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CharsetByDefaultMiddleware implements MiddlewareInterface
{
    /**
     * @var string default charset to use
     */
    private $www;

    /**
     * Configure the default charset.
     *
     * @param string $charset
     */
    public function __construct(string $charset = 'UTF-8')
    {
        // charset should have at least a length of 5 char, start with a letter and be alphanumeric with "_" or "-" as special char
        if (! preg_match('/^[a-z][a-z0-9_-]{4,}$/i', $charset)) {
            throw new InvalidArgumentException('Invalid charset value');
        }
        $this->charset = strtolower($charset);
    }

    /**
     * Process a request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        // TODO : ajouter la possibilité de courcircuité ce middleware si on utilise un charset = '' dans ce cas on ne doit rien faire. Ajouter les PHPunit associé à ce nouveau IF
        $response = $this->addDefaultCharset($response);

        return $response;
    }

    // @see : https://tools.ietf.org/html/rfc7231#section-3.1.1.2
    private function addDefaultCharset(ResponseInterface $response): ResponseInterface
    {
        $contentType = $response->hasHeader('Content-Type') ? $response->getHeaderLine('Content-Type') : null;

        if (! $contentType) {
            // add default content-type and charset
            // TODO : rendre le content-type par défaut réglable (dans notre cas on va toujours utiliser pas défaut 'text/html'). Ou alors créer un nouveau middleware qui serait appellé juste avant celui ci pour mettre uniquement le contenttype par défaut sans ajouter le charset.
            $response = $response->withHeader('Content-Type', 'text/html; charset=' . $this->charset);
        } elseif (stripos($contentType, 'charset') === false) {
            if ($this->isResponseTextual($contentType)) {
                // add the charset to the content-type header
                $response = $response->withHeader('Content-Type', $contentType . '; charset=' . $this->charset);
            }
        }

        return $response;
    }

    private function isResponseTextual(string $contentType): bool
    {
        // Charset could be used for textual representation, so we whitlist a bunch of representation who will be textual
        $whiteList = ['application/javascript', 'application/json', 'application/xml', 'application/rss+xml', 'application/atom+xml', 'application/xhtml', 'application/xhtml+xml'];

        // extract the media(mime) part from the Content-Type header
        $parts = explode(';', $contentType);
        $mediaType = strtolower(trim(array_shift($parts)));

        $isTextualOrWhitelisted = (stripos($contentType, 'text/') === 0 || in_array($mediaType, $whiteList));

        return $isTextualOrWhitelisted;
    }
}
