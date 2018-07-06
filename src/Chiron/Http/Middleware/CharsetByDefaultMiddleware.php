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
        // charset should have at least a length of 5 char, start with a letter and be alphanumeric and "_" or "-" as special char
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

        $response = $this->addDefaultCharset($response);

        return $response;
    }

    // @see : https://tools.ietf.org/html/rfc7231#section-3.1.1.2
    private function addDefaultCharset(ResponseInterface $response): ResponseInterface
    {
        $contentType = $response->hasHeader('Content-Type') ? $response->getHeaderLine('Content-Type') : null;

        if (! $contentType) {
            $response = $response->withHeader('Content-Type', 'text/html; charset=' . $this->charset);
        } elseif (stripos($contentType, 'charset') === false) {
            // Charset could be used for textual representation, so we whitlist a bunch of representation who will be textual
            $whitelist = ['application/javascript', 'application/json', 'application/xml', 'application/rss+xml', 'application/atom+xml', 'application/xhtml', 'application/xhtml+xml'];

            // TODO : attention pour le in_array on devrait spécifiquement extraire le mime du header content-type car on pourrait avoir un cas qui ne fonctionne pas si il y a d'autres infos que le mime. exemple : Content-Type: application/json; boundary=something
            if (stripos($contentType, 'text/') === 0 || in_array($this->getMediaType($response), $whitelist)) {
                // add the charset to the content-type header
                $response = $response->withHeader('Content-Type', $contentType . '; charset=' . $this->charset);
            }
        }

        return $response;
    }

    /**
     * Get request media type, if known.
     *
     * @param ServerRequestInterface $request request
     *
     * @return string|null The request media type, minus content-type params
     */
    // TODO : déplacer cettte méthode dans la classe MessageTrait car cela servira pour le serverrequest et pour la response ????
    private function getMediaType(ResponseInterface $response)
    {
        $contentType = $response->hasHeader('Content-Type') ? $response->getHeaderLine('Content-Type') : null;

        if ($contentType) {
            $parts = explode(';', $contentType);

            return strtolower(trim(array_shift($parts)));
        }
    }
}
