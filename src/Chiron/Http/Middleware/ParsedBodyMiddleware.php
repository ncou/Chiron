<?php

declare(strict_types=1);

namespace Chiron\Http\Middleware;

//https://github.com/phapi/middleware-postbox

// TODO : regarder ici : https://github.com/juliangut/body-parser/blob/master/src/Parser.php   +  https://github.com/juliangut/body-parser/tree/master/src/Decoder

//https://github.com/cakephp/cakephp/blob/master/src/Http/Middleware/BodyParserMiddleware.php

// TODO : regarder ici : https://github.com/phapi/middleware-postbox/blob/master/src/Phapi/Middleware/PostBox/PostBox.php
// TODO : regarder ici : https://github.com/relayphp/Relay.Middleware/blob/1.x/src/ContentHandler.php    +     https://github.com/relayphp/Relay.Middleware/blob/1.x/src/JsonContentHandler.php

// TODO : regarder ici : https://github.com/zendframework/zend-expressive-helpers/blob/master/src/BodyParams/BodyParamsMiddleware.php    +    https://github.com/zendframework/zend-expressive-helpers/blob/master/src/BodyParams/FormUrlEncodedStrategy.php  +    https://github.com/zendframework/zend-expressive-helpers/blob/master/src/BodyParams/JsonStrategy.php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ParsedBodyMiddleware implements MiddlewareInterface
{
    /**
     * Process request.
     *
     * @param ServerRequestInterface  $request request
     * @param RequestHandlerInterface $handler
     *
     * @return object ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // TODO : ajouter un test quand la méthode n'est pas la bonne
        // TODO : vérifier quelles méthodes ont un body !!!!!
        if (empty($request->getParsedBody()) && ! in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS']) && $request->hasHeader('Content-Type')) {
            $body = (string) $request->getBody();
            $parsedBody = null;

            $mediaType = $this->getMediaType($request);

            // Regex for : 'application/json' or 'application/*+json'
            //if (preg_match('~^application/([a-z.]+\+)?json($|;)~', $mediaType)) {
            //return (bool) preg_match('#[/+]json$#', trim($mime));
            if (preg_match('~application/([a-z.]+\+)?json~', $mediaType)) {
                $parsedBody = json_decode($body, true);
                if (! is_array($parsedBody)) {
                    // TODO : on devrait peut etre lever une exception 400 BadRequestHttpException
                    $parsedBody = null;
                }
                // Throw error if we are unable to decode body
                //if (is_null($parsed)) throw new BadRequest('Could not deserialize body (Json)');
            }

            // Regex for : 'application/xml' or 'application/*+xml' or 'text/xml'
            //if (preg_match('~^application/([a-z.]+\+)?xml($|;)~', $mediaType) || $mediaType === 'text/xml') {
            //if (preg_match('~^application/([a-z.]+\+)?xml~', $mediaType) || preg_match('~^text/xml~', $mediaType)) {
            //if (preg_match('~(application|text)/([a-z.]+\+)?xml~', $mediaType)) {
            if (preg_match('~application/([a-z.]+\+)?xml~', $mediaType) || $mediaType === 'text/xml') {
                // disable entity loading to prevent XXE (XML External Entity attacks) attacks
                $backup = libxml_disable_entity_loader(true);
                $backup_errors = libxml_use_internal_errors(true);
                // parse XML and disable internet connection when parsing XML
                //$parsed = simplexml_load_string($body);
                // TODO : regarder un autre exemple ici : https://github.com/yiisoft/yii2-httpclient/blob/master/src/XmlParser.php
                $parsedBody = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NONET);
                // restore lib settings
                libxml_disable_entity_loader($backup);
                libxml_clear_errors();
                libxml_use_internal_errors($backup_errors);
                if ($parsedBody === false) {
                    // TODO : on devrait peut etre lever une exception 400 BadRequestHttpException
                    $parsedBody = null;
                }
            }

            /*
            //return (bool) preg_match('#^application/x-www-form-urlencoded($|[ ;])#', $contentType);
                        $this->registerMediaTypeParser('application/x-www-form-urlencoded', function ($input) {
                            parse_str($input, $data);
                            return $data;
                        });
            */

            // in real life application, this part of code is not used because the request factory initialize the parsedBody with the global $_POST variable which is already parsed.
            if (preg_match('~application/x-www-form-urlencoded~', $mediaType)) {
                parse_str($body, $parsedBody);
            }

            // TODO : lever une exception 415 UnsupportedMediaTypeHttpException() si aucun deserializer n'est trouvé (cad si empty($parsedBody)=== true) ????
            // TODO : eventuellement créer un second middleware qui serai executé aprés, et si il y a un objet body non vide, mais un objet ParsedBody empty c'est qu'on n'a pas réussi à trouver un deserializer pour faire le travail et dans ce cas on leverai une exception !!!!

            $request = $request->withParsedBody($parsedBody);
        }

        return $handler->handle($request);
    }

    /**
     * Get request media type, if known.
     *
     * @param ServerRequestInterface $request request
     *
     * @return string|null The request media type, minus content-type params
     */
    // TODO : déplacer cettte méthode dans la classe MessageTrait car cela servira pour le serverrequest et pour la response ????
    private function getMediaType(ServerRequestInterface $request)
    {
        $contentType = $request->hasHeader('Content-Type') ? $request->getHeaderLine('Content-Type') : null;

        if ($contentType)
        {
            $parts = explode(';', $request->getHeaderLine('Content-Type'));

            return strtolower(trim(array_shift($parts)));
        }

        return null;
    }

    //************************************************

    /*
     * Parse the JSON response body and return an array
     *
     * @return array|string|int|bool|float
     * @throws RuntimeException if the response body is not in JSON format
     */
    /*
    public function json()
    {
        $data = json_decode((string) $this->body, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new RuntimeException('Unable to parse response body into JSON: ' . json_last_error());
        }
        return $data === null ? array() : $data;
    }*/
    /*
     * Parse the XML response body and return a \SimpleXMLElement.
     *
     * In order to prevent XXE attacks, this method disables loading external
     * entities. If you rely on external entities, then you must parse the
     * XML response manually by accessing the response body directly.
     *
     * @return \SimpleXMLElement
     * @throws RuntimeException if the response body is not in XML format
     * @link http://websec.io/2012/08/27/Preventing-XXE-in-PHP.html
     */
    /*
    public function xml()
    {
        $errorMessage = null;
        $internalErrors = libxml_use_internal_errors(true);
        $disableEntities = libxml_disable_entity_loader(true);
        libxml_clear_errors();
        try {
            $xml = new \SimpleXMLElement((string) $this->body ?: '<root />', LIBXML_NONET);
            if ($error = libxml_get_last_error()) {
                $errorMessage = $error->message;
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
        }
        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);
        libxml_disable_entity_loader($disableEntities);
        if ($errorMessage) {
            throw new RuntimeException('Unable to parse response body into XML: ' . $errorMessage);
        }
        return $xml;
    }*/

/*
    protected function detectFormatByHeaders(HeaderCollection $headers)
    {
        $contentTypeHeaders = $headers->get('content-type', null, false);
        if (!empty($contentTypeHeaders)) {
            $contentType = end($contentTypeHeaders);
            if (stripos($contentType, 'json') !== false) {
                return Client::FORMAT_JSON;
            }
            if (stripos($contentType, 'urlencoded') !== false) {
                return Client::FORMAT_URLENCODED;
            }
            if (stripos($contentType, 'xml') !== false) {
                return Client::FORMAT_XML;
            }
        }
        return null;
    }
*/

    /*
     * Deserialize the XML body
     *
     * @param $body
     * @return array
     * @throws BadRequest
     */
    //https://github.com/phapi/serializer-xml/blob/master/src/Phapi/Middleware/Deserializer/Xml/Xml.php#L38
    /*
    public function deserialize($body)
    {
        // Disable errors
        libxml_use_internal_errors(true);
        // Try and load the xml
        $xml = simplexml_load_string($body);
        // Check for errors
        if (count(libxml_get_errors()) > 0 || null === $array = json_decode(json_encode($xml), true)) {
            // Clear errors
            libxml_clear_errors();
            // Reset error handling
            libxml_use_internal_errors(false);
            // Throw exception
            throw new BadRequest('Could not deserialize body (XML)');
        }
        return $array;
    }*/

    /*
     * Parse the XML response body and return a \SimpleXMLElement.
     *
     * In order to prevent XXE attacks, this method disables loading external
     * entities. If you rely on external entities, then you must parse the
     * XML response manually by accessing the response body directly.
     *
     * @return \SimpleXMLElement
     * @throws RuntimeException if the response body is not in XML format
     * @link http://websec.io/2012/08/27/Preventing-XXE-in-PHP.html
     */
    /*
    //https://github.com/Guzzle3/http/blob/master/Message/Response.php#L878
    public function xml()
    {
        $errorMessage = null;
        $internalErrors = libxml_use_internal_errors(true);
        $disableEntities = libxml_disable_entity_loader(true);
        libxml_clear_errors();
        try {
            $xml = new \SimpleXMLElement((string) $this->body ?: '<root />', LIBXML_NONET);
            if ($error = libxml_get_last_error()) {
                $errorMessage = $error->message;
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
        }
        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);
        libxml_disable_entity_loader($disableEntities);
        if ($errorMessage) {
            throw new RuntimeException('Unable to parse response body into XML: ' . $errorMessage);
        }
        return $xml;
    }*/

/*
// TODO : méthode pour détecter si c'est du JSON depuis le content type.
    public function match($contentType)
    {
        $parts = explode(';', $contentType);
        $mime = array_shift($parts);
        return (bool) preg_match('#[/+]json$#', trim($mime));
    }

// TODO : json parser : https://github.com/zendframework/zend-expressive-helpers/blob/master/src/BodyParams/JsonStrategy.php
    public function parse(ServerRequestInterface $request)
    {
        $rawBody = (string) $request->getBody();
        $parsedBody = json_decode($rawBody, true);
        if (! empty($rawBody) && json_last_error() !== JSON_ERROR_NONE) {
            throw new MalformedRequestBodyException(sprintf(
                'Error when parsing JSON request body: %s',
                json_last_error_msg()
            ));
        }
        return $request
            ->withAttribute('rawBody', $rawBody)
            ->withParsedBody($parsedBody);
    }

    public function match($contentType)
    {
        return (bool) preg_match('#^application/x-www-form-urlencoded($|[ ;])#', $contentType);
    }

    public function parse(ServerRequestInterface $request)
    {
        $parsedBody = $request->getParsedBody();
        if (! empty($parsedBody)) {
            return $request;
        }
        $rawBody = (string) $request->getBody();
        if (empty($rawBody)) {
            return $request;
        }
        parse_str($rawBody, $parsedBody);
        return $request->withParsedBody($parsedBody);
    }
*/

    /*
     * Does HTTP method carry body content.
     *
     * @param string $method
     *
     * @return bool
     */
    /*
    private function methodCarriesBody($method)
    {
        return !in_array($method, ['GET', 'HEAD', 'OPTIONS', 'CONNECT', 'TRACE']);
    }*/

    /*
     * List of request methods that do not have any defined body semantics, and thus
     * will not have the body parsed.
     *
     * @see https://tools.ietf.org/html/rfc7231
     *
     * @var array
     */
    /*
    private $nonBodyRequests = [
        'GET',
        'HEAD',
        'OPTIONS',
    ];*/

    // GET, HEAD, DELETE, OPTIONS and CONNECT

    /*
     * The HTTP methods to parse data on.
     *
     * @var array
     */
    //protected $methods = ['PUT', 'POST', 'PATCH', 'DELETE'];
}
