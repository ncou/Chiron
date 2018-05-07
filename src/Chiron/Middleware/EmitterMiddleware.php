<?php

declare(strict_types=1);

namespace Chiron\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

//https://github.com/narrowspark/http-emitter/blob/master/src/AbstractSapiEmitter.php
//https://github.com/narrowspark/http-emitter/blob/master/src/SapiStreamEmitter.php

// TODO : regarder ici  https://github.com/ventoviro/windwalker-http/blob/master/Output/Output.php
// https://github.com/ventoviro/windwalker-http/blob/master/Output/StreamOutput.php

// TODO : regarder ici comment c'est fait : https://github.com/jasny/http-message/blob/master/src/Emitter.php

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
    /**
     * Handle the middleware pipeline call. This calls the next middleware
     * in the queue and after the rest of the middleware pipeline is done
     * the response will be sent to the client.
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

        // adjust the response headers to be RFC compliant
        $response = $this->finalizeResponse($response, $request);
        // Send response
        return $this->emit($response);
    }

    /*******************************************************************************
     * Send Response
     ******************************************************************************/

    // TODO : s'inpirer de cette classe : https://github.com/zendframework/zend-diactoros/blob/master/src/Response/SapiEmitterTrait.php

    /**
     * Emit the response (headers+body) to the client.
     *
     * @param ResponseInterface $response
     */
    // TODO : regarder dans la classe SAPIEMitter et ici comment c'est fait : https://github.com/http-interop/response-sender/blob/master/src/functions.php
    //TODO : attention il y a deux throw exception dans cette méthode qui ne seront pas catchés en amont et donc pas transformées en Response(500) !!!!!!!!!!!!
    public function emit(ResponseInterface $response): ResponseInterface
    {
        $this->sendHeaders($response);
        $this->sendBody($response);

        // Close connexion faster (module available if PHP-FPM mod is installed)
        if (function_exists('fastcgi_finish_request')) {
            \fastcgi_finish_request();
        }
        /*
              if (function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                } elseif ('cli' !== PHP_SAPI) {
                    static::closeOutputBuffers(0, true);
                }


              if (function_exists('fastcgi_finish_request')) {
                  @fastcgi_finish_request();
              }
        */
        return $response;
    }

    /**
     * Send HTTP Headers.
     *
     * @param ResponseInterface $response
     */
    public function sendHeaders(ResponseInterface $response): void
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

    // TODO : regarder comment c'est géré ici : https://github.com/symfony/http-foundation/blob/ed75b71c6498bd9c020dea99f723fd5b20aae986/Response.php#L336
    public function sendBody(ResponseInterface $response): void
    {
        set_time_limit(0); // Reset time limit for big files
        $chunkSize = 8 * 1024 * 1024; // 8MB per chunk
        //$chunkSize = 8 * 1024; // 8KB per chunk

        //https://github.com/http-interop/response-sender/blob/master/src/functions.php#L28
        $stream = $response->getBody();

        // rewind the stream in case the cursor il not at the start of the stream (if you have used ->getContent() before the cursor will be at the end of the stream)
        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        while (! $stream->eof()) {
            echo $stream->read($chunkSize);
            flush(); // Free up memory. Otherwise large files will trigger PHP's memory limit.
        }

        $stream->close();
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
    // TODO : méthode à déplacer dans la classe Response ???? OUI et à renommer en finalize ou prepare !!!!!
    // TODO : regarder plutot cette méthode "$response->prepare()" : https://github.com/symfony/http-foundation/blob/master/Response.php#L256
    // TODO : regarder ici : https://github.com/Hail-Team/framework/blob/7a314adbaa6216d1d1d3b3c44e172ce0cf65f978/src/Http/Response.php#L421
    // TODO : séparer la fonction finalize dans un middleware dédié, et regarder ici pour ajouter le "Expect" Header + content-type + content-length....etc : https://github.com/guzzle/guzzle/blob/master/src/PrepareBodyMiddleware.php
    private function finalizeResponse(ResponseInterface $response, ServerRequestInterface $request): ResponseInterface
    {
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
        || in_array($response->getStatusCode(), [204, 205, 304])) {
            // TODO : faire un helper pour vider le body d'une response.
            $response = $response->withoutHeader('Content-Type')->withoutHeader('Content-Length')->withBody(new \Chiron\Http\Stream(fopen('php://temp', 'r+')));
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

        return $response;
    }

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
    /*
    public static function closeOutputBuffers(int $targetLevel, bool $flush)
    {
        $status = ob_get_status(true);
        $level = count($status);
        $flags = PHP_OUTPUT_HANDLER_REMOVABLE | ($flush ? PHP_OUTPUT_HANDLER_FLUSHABLE : PHP_OUTPUT_HANDLER_CLEANABLE);
        while ($level-- > $targetLevel && ($s = $status[$level]) && (!isset($s['del']) ? !isset($s['flags']) || ($s['flags'] & $flags) === $flags : $s['del'])) {
            if ($flush) {
                ob_end_flush();
            } else {
                ob_end_clean();
            }
        }
    }*/
}
