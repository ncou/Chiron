<?php

declare(strict_types=1);

namespace Chiron\Http\Middleware;

//use Chiron\Http\Psr\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

//https://github.com/middlewares/negotiation/blob/master/src/ContentType.php

class ContentTypeByDefaultMiddleware implements MiddlewareInterface
{
    /**
     * Process a request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $this->addDefaultContentTypeInRequest($request);

        $response = $handler->handle($request);

        //ini_set('default_mimetype', '');
        $response = $this->addDefaultContentTypeInResponse($response);

        return $response;
    }

    // @see : https://tools.ietf.org/html/rfc7231#section-3.1.1.5
    private function addDefaultContentTypeInRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        if (! $request->hasHeader('Content-Type')) {
            $rawContent = (string) $request->getBody();

            if (! empty($rawContent)) {
                // add default content-type for the request if there is a body to sniff/analyze
                $contentType = $this->detectMimeBySniffingContent($rawContent);

                return $request->withHeader('Content-Type', $contentType);
            }
        }

        return $request;
    }

    private function addDefaultContentTypeInResponse(ResponseInterface $response): ResponseInterface
    {
        if (! $response->hasHeader('Content-Type')) {
            // add default content-type for the response
            $response = $response->withHeader('Content-Type', 'text/plain');
        }

        return $response;
    }

    /**
     * Detects response/request format from raw body content.
     *
     * @param string $content raw content to analyze
     *
     * @return string mime type name
     */
    private function detectMimeBySniffingContent(string $content): string
    {
        if (preg_match('/^\\{.*\\}$/is', $content)) {
            return 'application/json';
        }
        if (preg_match('/^([^=&])+=[^=&]+(&[^=&]+=[^=&]+)*$/', $content)) {
            return 'application/x-www-form-urlencoded';
        }
        if (preg_match('/^<.*>$/s', $content)) {
            return 'text/xml';
        }

        return 'application/octet-stream';
    }
}
