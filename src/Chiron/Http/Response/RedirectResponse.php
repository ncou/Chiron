<?php

declare(strict_types=1);

namespace Chiron\Http\Response;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use Chiron\Http\Psr\Response;
use Chiron\Http\Psr\Stream;
use function get_class;
use function gettype;
use function is_object;
use function is_string;
use function sprintf;
/**
 * Produce a redirect response.
 */
class RedirectResponse extends Response
{
    /**
     * Create a redirect response.
     *
     * Produces a redirect response with a Location header and the given status
     * (302 by default).
     *
     * Note: this method overwrites the `location` $headers value.
     *
     * @param string|UriInterface $uri URI for the Location header.
     * @param int $status Integer status code for the redirect; 302 by default.
     * @param array $headers Array of headers to use at initialization.
     */
    public function __construct($uri, int $status = 302, array $headers = [])
    {
        if (! is_string($uri) && ! $uri instanceof UriInterface) {
            throw new InvalidArgumentException(sprintf(
                'Uri provided to %s MUST be a string or Psr\Http\Message\UriInterface instance; received "%s"',
                __CLASS__,
                (is_object($uri) ? get_class($uri) : gettype($uri))
            ));
        }
        $headers['location'] = [(string) $uri];

        parent::__construct($status, $headers, new Stream(fopen('php://temp', 'r+')));
    }

    // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    // SYMFONY RESPONSE :
    // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

    protected $targetUrl;
    /**
     * Creates a redirect response so that it conforms to the rules defined for a redirect status code.
     *
     * @param string $url     The URL to redirect to. The URL should be a full URL, with schema etc.,
     *                        but practically every browser redirects on paths only as well
     * @param int    $status  The status code (302 by default)
     * @param array  $headers The headers (Location is always set to the given URL)
     *
     * @throws \InvalidArgumentException
     *
     * @see http://tools.ietf.org/html/rfc2616#section-10.3
     */
    /*
    public function __construct(?string $url, int $status = 302, array $headers = array())
    {
        parent::__construct('', $status, $headers);
        $this->setTargetUrl($url);
        if (!$this->isRedirect()) {
            throw new \InvalidArgumentException(sprintf('The HTTP status code is not a redirect ("%s" given).', $status));
        }
        if (301 == $status && !array_key_exists('cache-control', $headers)) {
            $this->headers->remove('cache-control');
        }
    }*/
    /**
     * Factory method for chainability.
     *
     * @param string $url     The url to redirect to
     * @param int    $status  The response status code
     * @param array  $headers An array of response headers
     *
     * @return static
     */
    public static function create($url = '', $status = 302, $headers = array())
    {
        return new static($url, $status, $headers);
    }
    /**
     * Returns the target URL.
     *
     * @return string target URL
     */
    public function getTargetUrl()
    {
        return $this->targetUrl;
    }
    /**
     * Sets the redirect target of this response.
     *
     * @param string $url The URL to redirect to
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setTargetUrl($url)
    {
        if (empty($url)) {
            throw new \InvalidArgumentException('Cannot redirect to an empty URL.');
        }
        $this->targetUrl = $url;
        $this->setContent(
            sprintf('<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="refresh" content="0;url=%1$s" />
        <title>Redirecting to %1$s</title>
    </head>
    <body>
        Redirecting to <a href="%1$s">%1$s</a>.
    </body>
</html>', htmlspecialchars($url, ENT_QUOTES, 'UTF-8')));
        $this->headers->set('Location', $url);
        return $this;
    }



    /**
     * Redirect.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * This method prepares the response object to return an HTTP Redirect
     * response to the client.
     *
     * @param string|UriInterface $url    the redirect destination
     * @param int|null            $status the redirect HTTP status code
     *
     * @return static
     */
    // TODO : vérifier ce code pour gérer le cas du 308 et 307 pour les redirections avec une méthode POST : https://github.com/middlewares/redirect/blob/master/src/Redirect.php#L89
    // TODO : utiliser une classe RedirectResponse et ajouter un body avec un lien hypertext (cf spec : https://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html#sec10.3.2), plus gestion du cache pour les redirections 301 : cf les classes Symfony.
    /*
    public function withRedirect($url, $status = null)
    {
        $responseWithRedirect = $this->withHeader('Location', (string) $url);
        if (is_null($status) && $this->getStatusCode() === 200) {
            $status = 302;
        }
        if (! is_null($status)) {
            // TODO : on devrait pas vérifier si le code est dans l'interval 3xx ?????
            $responseWithRedirect = $responseWithRedirect->withStatus($status);
        }

        // a message is better when doing a redirection.
        $urlHtml = htmlentities($url);
        $responseWithRedirect->getBody()->write('You are being redirected to <a href="' . $urlHtml . '">' . $urlHtml . '</a>', 'text/html');

        return $responseWithRedirect;
    }*/

}
