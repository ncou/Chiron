<?php

declare(strict_types=1);

namespace Chiron\Middleware;

use Chiron\Http\Factory\StreamFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

// Range : https://tools.ietf.org/html/rfc7233#section-4.3

//https://framework.zend.com/blog/2017-09-14-diactoros-emitters.html
//https://github.com/cakephp/cakephp/blob/master/src/Http/ResponseEmitter.php

// http://infinityquest.com/php-tutorials/program-http-range-in-php/     <== gestion des erreurs 416 quand le header "Range" n'est pas correct + un code 206 pour la response partielle.
//https://github.com/pomle/php-serveFilePartial/blob/master/ServeFilePartial.inc.php
//https://github.com/mukesh-kumar11/EasyCatalog/blob/aa028b4837ca961636938fea433ad39fdca7a74a/vendor/sabre/dav/lib/DAV/Server.php#L634
//https://github.com/mukesh-kumar11/EasyCatalog/blob/aa028b4837ca961636938fea433ad39fdca7a74a/vendor/sabre/dav/lib/DAV/CorePlugin.php#L152

//https://github.com/zendframework/zend-diactoros/blob/master/src/Response/SapiEmitter.php
//https://github.com/zendframework/zend-diactoros/blob/master/src/Response/SapiStreamEmitter.php

//https://github.com/narrowspark/http-emitter/blob/master/src/AbstractSapiEmitter.php
//https://github.com/narrowspark/http-emitter/blob/master/src/SapiStreamEmitter.php

// TODO : regarder ici  https://github.com/ventoviro/windwalker-http/blob/master/Output/Output.php
// https://github.com/ventoviro/windwalker-http/blob/master/Output/StreamOutput.php

// TODO : regarder ici comment c'est fait : https://github.com/jasny/http-message/blob/master/src/Emitter.php

// TODO : s'inpirer de cette classe : https://github.com/zendframework/zend-diactoros/blob/master/src/Response/SapiEmitterTrait.php
// TODO : regarder dans la classe SAPIEMitter et ici comment c'est fait : https://github.com/http-interop/response-sender/blob/master/src/functions.php

/**
 * Middleware Emitter.
 *
 * The Emitter middleware is responsible for taking the response
 * object and send the headers and body to the client.
 *
 * @category Phapi
 *
 * @author   Peter Ahinko <peter@ahinko.se>
 * @license  MIT (http://opensource.org/licenses/MIT)
 *
 * @see     https://github.com/phapi/middleware-courier
 */
class EmitterMiddleware implements MiddlewareInterface
{
    /** @var int max buffer size (8Kb) */
    private $maxBufferLength = 8 * 1024;

    /**
     * Emit the http response (headers+body) to the client.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param callable          $next
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        // As per RFC, HEAD request can't have a body.
        // Response to a HEAD request "MUST NOT" include a message-body
        /*
        if ($request->getMethod() === 'HEAD') {
            $response = $response->withBody(new NullStream());
        }*/

        // adjust the response headers to be RFC compliant
        // TODO : à virer et à remplacer par un middleware contentlenghtMiddleware + ne pas envoyer le body
        //$response = $this->finalizeResponse($response, $request);

        // Emit response (Headers + Status + Body)
        $this->emitHeaders($response);

        $range = $this->parseContentRange($response->getHeaderLine('Content-Range'));
        if (is_array($range) && $range[0] === 'bytes') {
            $this->emitBodyRange($range, $response, $this->maxBufferLength);
        } else {
            $this->emitBody($response, $this->maxBufferLength);
        }

        $this->closeConnexion();

        return $response;
    }

    public function setMaxBufferLength(int $length): self
    {
        $this->maxBufferLength = $length;

        return $this;
    }

    /**
     * Emit the status line.
     *
     * Emits the status line using the protocol version and status code from
     * the response; if a reason phrase is available, it, too, is emitted.
     *
     * @param \Psr\Http\Message\ResponseInterface $response The response to emit
     */
    /*
    protected function emitStatusLine(ResponseInterface $response)
    {
        $reasonPhrase = $response->getReasonPhrase();
        header(sprintf(
            'HTTP/%s %d%s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            ($reasonPhrase ? ' ' . $reasonPhrase : '')
        ));
    }*/

    /**
     * Send HTTP Headers.
     *
     * @param ResponseInterface $response
     */
    private function emitHeaders(ResponseInterface $response): void
    {
        /*
        if (headers_sent($file, $line)) {
            throw new \RuntimeException(sprintf('Failed to send headers, because headers have already been sent by "%s" at line %d.', $file, $line));
        }*/
        // headers have already been sent by the developer
        if (headers_sent()) {
            return;
        }

        $statusCode = $response->getStatusCode();

        $replaceSameHeaders = false;

        // TODO : regarder ici pour voir comment on emet les cookies !!!!! https://github.com/zendframework/zend-diactoros/blob/master/src/Response/SapiEmitterTrait.php#L78
        // TODO : regarder ici, car un header peut avoir un tableau de valeurs, dans le cas ou on a mergé 2 headers identiques !!!!   https://github.com/slimphp/Slim/blob/3.x/Slim/App.php#L393
        // headers
        foreach ($response->getHeaders() as $header => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $header, $value), $replaceSameHeaders, $statusCode);
            }
        }

        // TODO : gérer le cas ou il n'y a pas de ReasonPhrase et mettre une chaine vide : https://github.com/zendframework/zend-diactoros/blob/master/src/Response/SapiEmitterTrait.php#L55
        // Set proper protocol, status code (and reason phrase) header
        /*
        if ($response->getReasonPhrase()) {
            header(sprintf(
                'HTTP/%s %d %s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase()
            ));
        } else {
            header(sprintf(
                'HTTP/%s %d',
                $response->getProtocolVersion(),
                $response->getStatusCode()
            ));
        }*/

        $replaceSameHeaders = true;
        // It is important to mention that this method should be called after the headers are sent, in order to prevent PHP from changing the status code of the emitted response.
        header(sprintf('HTTP/%s %d %s', $response->getProtocolVersion(), $statusCode, $response->getReasonPhrase()), $replaceSameHeaders, $statusCode);

        // cookies
//TODO : utiliser les cookies comme des "headers" classiques ('Set-Cookies:xxxxxxx')
//https://github.com/paragonie/PHP-Cookie/blob/master/src/Cookie.php#L358

//        foreach ($response->getCookies() as $cookie) {
//          setrawcookie($cookie['name'], $cookie['value'], $cookie['expire'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
//        }

//        flush();
//      }

      // cookies
      /*
      foreach ($this->headers->getCookies() as $cookie) {
          if ($cookie->isRaw()) {
              setrawcookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
          } else {
              setcookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
          }
      }*/
    }

    /**
     * Emit the message body.
     *
     * @param \Psr\Http\Message\ResponseInterface $response        The response to emit
     * @param int                                 $maxBufferLength The chunk size to emit
     */
    // TODO : regarder comment c'est géré ici : https://github.com/symfony/http-foundation/blob/ed75b71c6498bd9c020dea99f723fd5b20aae986/Response.php#L336
    private function emitBody(ResponseInterface $response, int $chunkSize): void
    {
        // exit if the response doesn't require a body
        // TODO : remplacer ce test par un $response->isInformational() et $response->isEmpty()
        if (static::isBodyEmpty($response)) {
            return;
        }

        //set_time_limit(0); // Reset time limit for big files
        //$chunkSize = 8 * 1024 * 1024; // 8MB per chunk
        //$chunkSize = 8 * 1024; // 8KB per chunk

        //https://github.com/http-interop/response-sender/blob/master/src/functions.php#L28
        $body = $response->getBody();

        // rewind the stream in case the cursor is not at the start of the stream (if you have used ->getContent() before the cursor will be at the end of the stream)
        if ($body->isSeekable()) {
            $body->rewind();
        }

        if (! $body->isReadable()) {
            echo $body;

            return;
        }

        while (! $body->eof()) {
            echo $body->read($chunkSize);
            //flush(); // Free up memory. Otherwise large files will trigger PHP's memory limit.
        }

        //$body->close();
    }

    /**
     * Emit a range of the message body.
     *
     * @param array             $range
     * @param ResponseInterface $response
     * @param int               $maxBufferLength
     */
    private function emitBodyRange(array $range, ResponseInterface $response, int $chunkSize): void
    {
        list($unit, $first, $last, $length) = $range;
        $body = $response->getBody();
        $length = $last - $first + 1;

        if ($body->isSeekable()) {
            $body->seek($first);
            $first = 0;
        }

        if (! $body->isReadable()) {
            echo substr($body->getContents(), $first, $length);

            return;
        }

        $remaining = $length;
        while ($remaining >= $chunkSize && ! $body->eof()) {
            $contents = $body->read($chunkSize);
            $remaining -= strlen($contents);
            echo $contents;
        }

        if ($remaining > 0 && ! $body->eof()) {
            echo $body->read($remaining);
        }
    }

    private function closeConnexion(): void
    {
        // FastCGI, close connexion faster (module available if PHP-FPM mod is installed)
        if (function_exists('fastcgi_finish_request')) {
            \fastcgi_finish_request();
        } elseif ('cli' !== PHP_SAPI) {
            static::closeOutputBuffers(0, true);
        }
    }

    /*
    //https://github.com/Wandu/Http/blob/master/Sender/ResponseSender.php#L12
        public function respond(ResponseInterface $response)
        {
            $statusCode = $response->getStatusCode();
            $reasonPhrase = $response->getReasonPhrase();
            $protocolVersion = $response->getProtocolVersion();
            header("HTTP/{$protocolVersion} $statusCode $reasonPhrase", true, $statusCode);
            foreach ($response->getHeaders() as $name => $values) {
                if (strtolower($name) === 'set-cookie') {
                    foreach ($values as $cookie) {
                        header(sprintf('Set-Cookie: %s', $cookie), false);
                    }
                    break;
                }
                header(sprintf('%s: %s', $name, $response->getHeaderLine($name)));
            }
            $body = $response->getBody();
            if ($body) {
                // faster and less memory!
                if ($body instanceof Traversable) {
                    foreach ($body as $contents) {
                        echo $contents;
                    }
                } else {
                    echo $body->__toString();
                }
            }
        }
    */

    /**
     * Prepares the Response before it is sent to the client.
     *
     * This method tweaks the Response to ensure that it is
     * compliant with RFC 2616. Most of the changes are based on
     * the Request that is "associated" with this Response.
     *
     * @param ResponseInterface      $response
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    // TODO : regarder aussi ici : https://github.com/cakephp/cakephp/blob/master/src/Http/Response.php#L593   notamment pour le charset !!!!!
    // TODO : méthode à déplacer dans la classe Response ???? OUI et à renommer en finalize ou prepare !!!!!
    // TODO : regarder plutot cette méthode "$response->prepare()" : https://github.com/symfony/http-foundation/blob/master/Response.php#L256
    // TODO : regarder ici : https://github.com/Hail-Team/framework/blob/7a314adbaa6216d1d1d3b3c44e172ce0cf65f978/src/Http/Response.php#L421
    // TODO : séparer la fonction finalize dans un middleware dédié, et regarder ici pour ajouter le "Expect" Header + content-type + content-length....etc : https://github.com/guzzle/guzzle/blob/master/src/PrepareBodyMiddleware.php
    private function finalizeResponse(ResponseInterface $response, ServerRequestInterface $request): ResponseInterface
    {
        /*
                if ($this->hasHeader('Location') && $this->_status === 200) {
                    $this->statusCode(302);
                }
        */

        /*
                if (!isset($this->headers['cache-control'])) {
                    $this->set('Cache-Control', '');
                }
        */

        //TODO : pas sur que cela serve car apache semble ajouter automatiquement la date du jour et on ne peut pas la modifier... :(
        /* RFC2616 - 14.18 says all Responses need to have a Date */
        if (! $response->hasHeader('Date')) {
            $response = $response->withHeader('Date', $this->initDate());
        }

        // add default header text/html
        //if (! $response->hasHeader('Content-Type')) {
        //  $response = $response->withHeader('Content-Type', 'text/html');
        //}

        /*
              $container = $this->getContainer();
              // Fix Content-Type
              $charset = $container['charset'] ? $container['charset'] : 'UTF-8';
        */
        // Fix Content-Type
        $charset = 'UTF-8';
        if (! $response->hasHeader('Content-Type')) {
            $response = $response->withHeader('Content-Type', 'text/html; charset=' . $charset);
        } elseif (0 === stripos($response->getHeaderLine('Content-Type'), 'text/') && false === stripos($response->getHeaderLine('Content-Type'), 'charset')) {
            // add the charset
            $response = $response->withHeader('Content-Type', $response->getHeaderLine('Content-Type') . '; charset=' . $charset);
        }

        if (! $response->hasHeader('Content-Length')) {
            $response = $response->withHeader('Content-Length', (string) $response->getBody()->getSize());
        }

        // check if the response can have a body or not
        // TODO : externaliser ce test "if" dans l'objet Response et faire une méthode "isEmpty()" qui vérifie si le code = 204/205 ou 304
        // TODO : on doit faire la même chose (virer contenttype et contententlength et vider le body) si la réponse est "informational" cad avec un code entre
        if (($response->getStatusCode() >= 100 && $response->getStatusCode() < 200)
        || in_array($response->getStatusCode(), [204, 304])) {
            // TODO : faire un helper pour vider le body d'une response.
            $response = $response->withoutHeader('Content-Type')->withoutHeader('Content-Length')->withBody(StreamFactory::createFromStringOrResource(fopen('php://temp', 'r+')));
            //return;
        }

        // Fix protocol (response will use the same value as the request)
        // TODO : pourquoi on fait pas simplement un $response->setProcotolVersion($request->getProtocolVersion()) ?????
        /*
        preg_match('~^HTTP/([1-9]\.[0-9])$~', $request->server->get('SERVER_PROTOCOL'), $versionMatches);
        if ($versionMatches) {
          $response->setProtocolVersion($versionMatches[1]);
        }*/

        // empty the body if the http method is HEAD (same response as GET but with an empty body) https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
        if ($request->isMethod('HEAD')) {
            // cf. RFC2616 14.13
            $length = $response->getHeaderLine('Content-Length');
            // TODO : regarder si on peut mettre un body à null (cad que cela fasse la même chose qu'une chaine vide '')
            $response->setBody('');
            if ($length) {
                $response = $response->withHeader('Content-Length', $length);
            }
        }

        /* According to RFC2616 section 4.4, we MUST ignore
             Content-Length: headers if we are now receiving data
             using chunked Transfer-Encoding.
          */
        //if ($headers->has('Transfer-Encoding')) {
        //    $headers->remove('Content-Length');
        //}

        /*
                     As Lukas alludet to, HTTP 1.1 prohibits Content-Length if there's a Transfer-Encoding set.

        Quoting http://www.ietf.org/rfc/rfc2616.txt:

           3.If a Content-Length header field (section 14.13) is present, its
             decimal value in OCTETs represents both the entity-length and the
             transfer-length. The Content-Length header field MUST NOT be sent
             if these two lengths are different (i.e., if a Transfer-Encoding
             header field is present). If a message is received with both a
             Transfer-Encoding header field and a Content-Length header field,
             the latter MUST be ignored.
             */

        // TODO : regarder pourquoi cakephp ajout le contenttype uniquement si on a du plain-text !!!!!
        //https://github.com/cakephp/cakephp/blob/master/src/Http/Response.php#L587

        return $response;
    }

    /**
     * Formats the Content-Type header based on the configured contentType and charset
     * the charset will only be set in the header if the response is of type text/*.
     */
    /*
    protected function _setContentType()
    {
        if (in_array($this->_status, [304, 204])) {
            $this->_clearHeader('Content-Type');
            return;
        }
        $whitelist = [
            'application/javascript', 'application/json', 'application/xml', 'application/rss+xml'
        ];
        $charset = false;
        if ($this->_charset &&
            (strpos($this->_contentType, 'text/') === 0 || in_array($this->_contentType, $whitelist))
        ) {
            $charset = true;
        }
        if ($charset) {
            $this->_setHeader('Content-Type', "{$this->_contentType}; charset={$this->_charset}");
        } else {
            $this->_setHeader('Content-Type', (string)$this->_contentType);
        }
    }*/

    private function initDate()
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $now = \DateTimeImmutable::createFromMutable($now);

        return $now->format('D, d M Y H:i:s') . ' GMT';
    }

    /**
     * Prepares the Response before it is sent to the client.
     *
     * This method tweaks the Response to ensure that it is
     * compliant with RFC 2616. Most of the changes are based on
     * the Request that is "associated" with this Response.
     *
     * @param ResponseInterface      $response
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    private function finalize(ResponseInterface $response, ServerRequestInterface $request): ResponseInterface
    {
        $headers = $response->headers;
        if ($response->isInformational() || $response->isEmpty()) {
            $response->setContent(null);
            $headers->remove('Content-Type');
            $headers->remove('Content-Length');
        } else {
            // Content-type based on the Request
            if (! $headers->has('Content-Type')) {
                $format = $request->getRequestFormat();
                if (null !== $format && $mimeType = $request->getMimeType($format)) {
                    $headers->set('Content-Type', $mimeType);
                }
            }
            // Fix Content-Type
            $charset = $response->charset ?: 'UTF-8';
            if (! $headers->has('Content-Type')) {
                $headers->set('Content-Type', 'text/html; charset=' . $charset);
            } elseif (0 === stripos($headers->get('Content-Type'), 'text/') && false === stripos($headers->get('Content-Type'), 'charset')) {
                // add the charset
                $headers->set('Content-Type', $headers->get('Content-Type') . '; charset=' . $charset);
            }
            // Fix Content-Length
            if ($headers->has('Transfer-Encoding')) {
                $headers->remove('Content-Length');
            }
            if ($request->isMethod('HEAD')) {
                // cf. RFC2616 14.13
                $length = $headers->get('Content-Length');
                $response->setContent(null);
                if ($length) {
                    $headers->set('Content-Length', $length);
                }
            }
        }
        // Fix protocol
        if ('HTTP/1.0' != $request->server->get('SERVER_PROTOCOL')) {
            $response->setProtocolVersion('1.1');
        }
        // Check if we need to send extra expire info headers
        if ('1.0' == $response->getProtocolVersion() && false !== strpos($response->headers->get('Cache-Control'), 'no-cache')) {
            $response->headers->set('pragma', 'no-cache');
            $response->headers->set('expires', -1);
        }

        return $response;
    }

    /*
     * Cleans or flushes output buffers up to target level.
     *
     * Resulting level can be greater than target level if a non-removable buffer has been encountered.
     *
     * @final
     */
    private static function closeOutputBuffers(int $targetLevel, bool $flush)
    {
        $status = ob_get_status(true);
        $level = count($status);
        $flags = PHP_OUTPUT_HANDLER_REMOVABLE | ($flush ? PHP_OUTPUT_HANDLER_FLUSHABLE : PHP_OUTPUT_HANDLER_CLEANABLE);
        while ($level-- > $targetLevel && ($s = $status[$level]) && (! isset($s['del']) ? ! isset($s['flags']) || ($s['flags'] & $flags) === $flags : $s['del'])) {
            if ($flush) {
                ob_end_flush();
            } else {
                ob_end_clean();
            }
        }

        //while (@ob_end_flush());
        /*
        while (ob_get_level() > 0) {
            ob_end_flush();
        }*/
    }

    private static function isBodyEmpty(ResponseInterface $response): bool
    {
        // All 1xx (informational), 204 (no content), and 304 (not modified) responses MUST NOT include a message-body
        return ($response->getStatusCode() >= 100 && $response->getStatusCode() < 200) || in_array($response->getStatusCode(), [204, 304]);
    }

    /**
     * Checks to see if content has previously been sent.
     *
     * If either headers have been sent or the output buffer contains content,
     * raises an exception.
     *
     * @throws RuntimeException if headers have already been sent.
     * @throws RuntimeException if output is present in the output buffer.
     */
    /*
    private function assertNoPreviousOutput()
    {
        if (headers_sent()) {
            throw new RuntimeException('Unable to emit response; headers already sent');
        }
        if (ob_get_level() > 0 && ob_get_length() > 0) {
            throw new RuntimeException('Output has been emitted previously; cannot emit response');
        }
    }*/

    /**
     * Parse content-range header
     * https://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.16.
     *
     * @param string $header The Content-Range header to parse.
     *
     * @return false|array [unit, first, last, length]; returns false if no
     *                     content range or an invalid content range is provided
     */
    protected function parseContentRange($header)
    {
        if (preg_match('/(?P<unit>[\w]+)\s+(?P<first>\d+)-(?P<last>\d+)\/(?P<length>\d+|\*)/', $header, $matches)) {
            return [
                $matches['unit'],
                (int) $matches['first'],
                (int) $matches['last'],
                $matches['length'] === '*' ? '*' : (int) $matches['length'],
            ];
        }

        return false;
    }
}
