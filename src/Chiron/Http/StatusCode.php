<?php

declare(strict_types=1);

namespace Chiron\Http;

use Chiron\Http\Exception\Client\BadRequestHttpException;
use Chiron\Http\Exception\Client\ConflictHttpException;
use Chiron\Http\Exception\Client\ExpectationFailedHttpException;
use Chiron\Http\Exception\Client\FailedDependencyHttpException;
use Chiron\Http\Exception\Client\ForbiddenHttpException;
use Chiron\Http\Exception\Client\GoneHttpException;
use Chiron\Http\Exception\Client\ImATeapotHttpException;
use Chiron\Http\Exception\Client\LengthRequiredHttpException;
use Chiron\Http\Exception\Client\LockedHttpException;
use Chiron\Http\Exception\Client\MethodNotAllowedHttpException;
use Chiron\Http\Exception\Client\MisdirectedRequestHttpException;
use Chiron\Http\Exception\Client\NotAcceptableHttpException;
use Chiron\Http\Exception\Client\NotFoundHttpException;
use Chiron\Http\Exception\Client\PayloadTooLargeHttpException;
use Chiron\Http\Exception\Client\PaymentRequiredHttpException;
use Chiron\Http\Exception\Client\PreconditionFailedHttpException;
use Chiron\Http\Exception\Client\PreconditionRequiredHttpException;
use Chiron\Http\Exception\Client\ProxyAuthenticationRequiredHttpException;
use Chiron\Http\Exception\Client\RequestedRangeNotSatisfiableHttpException;
use Chiron\Http\Exception\Client\RequestHeaderFieldsTooLargeHttpException;
use Chiron\Http\Exception\Client\RequestTimeoutHttpException;
use Chiron\Http\Exception\Client\RequestUriTooLongHttpException;
use Chiron\Http\Exception\Client\TooEarlyRequestHttpException;
use Chiron\Http\Exception\Client\TooManyRequestsHttpException;
use Chiron\Http\Exception\Client\UnauthorizedHttpException;
use Chiron\Http\Exception\Client\UnavailableForLegalReasonsHttpException;
use Chiron\Http\Exception\Client\UnprocessableEntityHttpException;
use Chiron\Http\Exception\Client\UnsupportedMediaTypeHttpException;
use Chiron\Http\Exception\Client\UpgradeRequiredHttpException;
use Chiron\Http\Exception\Server\BadGatewayHttpException;
use Chiron\Http\Exception\Server\GatewayTimeoutHttpException;
use Chiron\Http\Exception\Server\HttpVersionNotSupportedHttpException;
use Chiron\Http\Exception\Server\InsufficientStorageHttpException;
use Chiron\Http\Exception\Server\InternalServerErrorHttpException;
use Chiron\Http\Exception\Server\LoopDetectedHttpException;
use Chiron\Http\Exception\Server\NetworkAuthenticationRequiredHttpException;
use Chiron\Http\Exception\Server\NotExtendedHttpException;
use Chiron\Http\Exception\Server\NotImplementedHttpException;
use Chiron\Http\Exception\Server\ServiceUnavailableHttpException;
use Chiron\Http\Exception\Server\VariantAlsoNegotiatesHttpException;

// TODO : renommer cette classe en HttpStatus ?????
class StatusCode
{
    /**
     * Allowed range for a valid HTTP status code.
     */
    // TODO : renommer en MINIMUM_CODE_VALUE et MAXIMUM_CODE_VALUE ????
    public const MIN_STATUS_CODE_VALUE = 100;
    public const MAX_STATUS_CODE_VALUE = 599;

    /* Http Status Code, http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml */
    public const HTTP_CONTINUE = 100;
    public const HTTP_SWITCHING_PROTOCOLS = 101;
    public const HTTP_PROCESSING = 102;
    public const HTTP_EARLY_HINTS = 103;
    public const HTTP_OK = 200;
    public const HTTP_CREATED = 201;
    public const HTTP_ACCEPTED = 202;
    public const HTTP_NONAUTHORITATIVE_INFORMATION = 203;
    public const HTTP_NO_CONTENT = 204;
    public const HTTP_RESET_CONTENT = 205;
    public const HTTP_PARTIAL_CONTENT = 206;
    public const HTTP_MULTI_STATUS = 207;
    public const HTTP_ALREADY_REPORTED = 208;
    public const HTTP_IM_USED = 226;
    public const HTTP_MULTIPLE_CHOICES = 300;
    public const HTTP_MOVED_PERMANENTLY = 301;
    public const HTTP_FOUND = 302;
    public const HTTP_SEE_OTHER = 303;
    public const HTTP_NOT_MODIFIED = 304;
    public const HTTP_USE_PROXY = 305;
    public const HTTP_UNUSED = 306;
    public const HTTP_TEMPORARY_REDIRECT = 307;
    public const HTTP_PERMANENT_REDIRECT = 308;
    public const HTTP_BAD_REQUEST = 400;
    public const HTTP_UNAUTHORIZED = 401;
    public const HTTP_PAYMENT_REQUIRED = 402;
    public const HTTP_FORBIDDEN = 403;
    public const HTTP_NOT_FOUND = 404;
    public const HTTP_METHOD_NOT_ALLOWED = 405;
    public const HTTP_NOT_ACCEPTABLE = 406;
    public const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    public const HTTP_REQUEST_TIMEOUT = 408;
    public const HTTP_CONFLICT = 409;
    public const HTTP_GONE = 410;
    public const HTTP_LENGTH_REQUIRED = 411;
    public const HTTP_PRECONDITION_FAILED = 412;
    public const HTTP_PAYLOAD_TOO_LARGE = 413;
    public const HTTP_URI_TOO_LONG = 414;
    public const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    public const HTTP_RANGE_NOT_SATISFIABLE = 416;
    public const HTTP_EXPECTATION_FAILED = 417;
    public const HTTP_IM_A_TEAPOT = 418;
    public const HTTP_MISDIRECTED_REQUEST = 421;
    public const HTTP_UNPROCESSABLE_ENTITY = 422;
    public const HTTP_LOCKED = 423;
    public const HTTP_FAILED_DEPENDENCY = 424;
    public const HTTP_TOO_EARLY = 425;
    public const HTTP_UPGRADE_REQUIRED = 426;
    public const HTTP_PRECONDITION_REQUIRED = 428;
    public const HTTP_TOO_MANY_REQUESTS = 429;
    public const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    public const HTTP_UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    public const HTTP_INTERNAL_SERVER_ERROR = 500;
    public const HTTP_NOT_IMPLEMENTED = 501;
    public const HTTP_BAD_GATEWAY = 502;
    public const HTTP_SERVICE_UNAVAILABLE = 503;
    public const HTTP_GATEWAY_TIMEOUT = 504;
    public const HTTP_VERSION_NOT_SUPPORTED = 505;
    public const HTTP_VARIANT_ALSO_NEGOTIATES = 506;
    public const HTTP_INSUFFICIENT_STORAGE = 507;
    public const HTTP_LOOP_DETECTED = 508;
    public const HTTP_NOT_EXTENDED = 510;
    public const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;

    /**
     * Array Map of standard HTTP status code/reason phrases.
     *
     * @var array
     */
    // TODO : utiliser les constantes définies précédemment dans la classe (ex : remplacer '400' par self::HTTP_BAD_REQUEST)
    // TODO : à renommer en $statusTexts ????
    private static $statusNames = [
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        // Successful 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    /**
     * Array of standard HTTP status code/reason phrases.
     *
     * @see https://en.wikipedia.org/wiki/List_of_HTTP_status_codes
     *
     * @var array
     */
    // TODO : utiliser les constantes définies précédemment dans la classe (ex : remplacer '400' par self::HTTP_BAD_REQUEST)
    private static $errorPhrases = [
        // Successful 2xx
        200 => 'Standard response for successful HTTP requests.',
        201 => 'The request has been fulfilled, resulting in the creation of a new resource.',
        202 => 'The request has been accepted for processing, but the processing has not been completed.',
        203 => 'The server is a transforming proxy (e.g. a Web accelerator) that received a 200 OK from its origin, but is returning a modified version of the origin\'s response.',
        204 => 'The server successfully processed the request and is not returning any content.',
        205 => 'The server successfully processed the request, but is not returning any content.',
        206 => 'The server is delivering only part of the resource (byte serving) due to a range header sent by the client.',
        207 => 'The message body that follows is an XML message and can contain a number of separate response codes, depending on how many sub-requests were made.',
        208 => 'The members of a DAV binding have already been enumerated in a previous reply to this request, and are not being included again.',
        226 => 'The server has fulfilled a request for the resource, and the response is a representation of the result of one or more instance-manipulations applied to the current instance.',
        // Redirection 3xx
        300 => 'Indicates multiple options for the resource from which the client may choose.',
        301 => 'This and all future requests should be directed to the given URI.',
        302 => 'This is an example of industry practice contradicting the standard.',
        303 => 'The response to the request can be found under another URI using a GET method.',
        304 => 'Indicates that the resource has not been modified since the version specified by the request headers If-Modified-Since or If-None-Match.',
        305 => 'The requested resource is available only through a proxy, the address for which is provided in the response.',
        306 => 'No longer used.',
        307 => 'In this case, the request should be repeated with another URI; however, future requests should still use the original URI.',
        308 => 'The request and all future requests should be repeated using another URI.',
        // Client Error 4xx
        400 => 'The request cannot be fulfilled due to bad syntax.',
        401 => 'Authentication is required and has failed or has not yet been provided.',
        402 => 'Reserved for future use.',
        403 => 'The request was a valid request, but the server is refusing to respond to it.',
        404 => 'The requested resource could not be found but may be available again in the future.',
        405 => 'A request was made of a resource using a request method not supported by that resource.',
        406 => 'The requested resource is only capable of generating content not acceptable.',
        407 => 'Proxy authentication is required to access the requested resource.',
        408 => 'The server did not receive a complete request message in time.',
        409 => 'The request could not be processed because of conflict in the request.',
        410 => 'The requested resource is no longer available and will not be available again.',
        411 => 'The request did not specify the length of its content, which is required by the resource.',
        412 => 'The server does not meet one of the preconditions that the requester put on the request.',
        413 => 'The server cannot process the request because the request payload is too large.',
        414 => 'The request-target is longer than the server is willing to interpret.',
        415 => 'The request entity has a media type which the server or resource does not support.',
        416 => 'The client has asked for a portion of the file, but the server cannot supply that portion.',
        417 => 'The expectation given could not be met by at least one of the inbound servers.',
        418 => 'I\'m a teapot',
        421 => 'The request was directed at a server that is not able to produce a response.',
        422 => 'The request was well-formed but was unable to be followed due to semantic errors.',
        423 => 'The resource that is being accessed is locked.',
        424 => 'The request failed due to failure of a previous request.',
        425 => 'The request could not be processed due to the consequences of a possible replay attack.',
        426 => 'The server cannot process the request using the current protocol.',
        428 => 'The origin server requires the request to be conditional.',
        429 => 'The user has sent too many requests in a given amount of time.',
        431 => 'The server is unwilling to process the request because either an individual header field, or all the header fields collectively, are too large.',
        451 => 'Resource access is denied for legal reasons.',
        // Server Error 5xx
        500 => 'An error has occurred and this resource cannot be displayed.',
        501 => 'The server either does not recognize the request method, or it lacks the ability to fulfil the request.',
        502 => 'The server was acting as a gateway or proxy and received an invalid response from the upstream server.',
        503 => 'The server is currently unavailable. It may be overloaded or down for maintenance.',
        504 => 'The server was acting as a gateway or proxy and did not receive a timely response from the upstream server.',
        505 => 'The server does not support the HTTP protocol version used in the request.',
        506 => 'Transparent content negotiation for the request, results in a circular reference.',
        507 => 'The method could not be performed on the resource because the server is unable to store the representation needed to successfully complete the request. There is insufficient free space left in your storage allocation.',
        508 => 'The server detected an infinite loop while processing the request.',
        510 => 'Further extensions to the request are required for the server to fulfill it.A mandatory extension policy in the request is not accepted by the server for this resource.',
        511 => 'The client needs to authenticate to gain network access.',
    ];

    /**
     * Array of mapping beetween Exceptions Name and the Client/Server error status code.
     *
     * @var array
     */
    // TODO : utiliser les constantes définies précédemment dans la classe (ex : remplacer '400' par self::HTTP_BAD_REQUEST)
    // TODO : regarder ici pour utiliser un namespace generic lors de l'initialisation de la classe : https://github.com/ncou/http-HttpException/blob/master/src/Http/Exception/Factory.php#L58
    private static $mapExceptionName = [
        400 => BadRequestHttpException::class,
        401 => UnauthorizedHttpException::class,
        402 => PaymentRequiredHttpException::class,
        403 => ForbiddenHttpException::class,
        404 => NotFoundHttpException::class,
        405 => MethodNotAllowedHttpException::class,
        406 => NotAcceptableHttpException::class,
        407 => ProxyAuthenticationRequiredHttpException::class,
        408 => RequestTimeoutHttpException::class,
        409 => ConflictHttpException::class,
        410 => GoneHttpException::class,
        411 => LengthRequiredHttpException::class,
        412 => PreconditionFailedHttpException::class,
        413 => PayloadTooLargeHttpException::class,
        414 => RequestUriTooLongHttpException::class,
        415 => UnsupportedMediaTypeHttpException::class,
        416 => RequestedRangeNotSatisfiableHttpException::class,
        417 => ExpectationFailedHttpException::class,
        418 => ImATeapotHttpException::class,
        421 => MisdirectedRequestHttpException::class,
        422 => UnprocessableEntityHttpException::class,
        423 => LockedHttpException::class,
        424 => FailedDependencyHttpException::class,
        425 => TooEarlyRequestHttpException::class,
        426 => UpgradeRequiredHttpException::class,
        428 => PreconditionRequiredHttpException::class,
        429 => TooManyRequestsHttpException::class,
        431 => RequestHeaderFieldsTooLargeHttpException::class,
        451 => UnavailableForLegalReasonsHttpException::class,
        500 => InternalServerErrorHttpException::class,
        501 => NotImplementedHttpException::class,
        502 => BadGatewayHttpException::class,
        503 => ServiceUnavailableHttpException::class,
        504 => GatewayTimeoutHttpException::class,
        505 => HttpVersionNotSupportedHttpException::class,
        506 => VariantAlsoNegotiatesHttpException::class,
        507 => InsufficientStorageHttpException::class,
        508 => LoopDetectedHttpException::class,
        510 => NotExtendedHttpException::class,
        511 => NetworkAuthenticationRequiredHttpException::class,
    ];

    /**
     * Private constructor; non-instantiable use only the static methods !
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Get the message for a given status code.
     *
     * @param int $code http status code
     *
     * @throws \InvalidArgumentException If the requested $code is not valid
     * @throws \OutOfBoundsException     If the requested $code is not found
     *
     * @return string Returns message for the given status code
     */
    public static function getReasonMessage(int $code): string
    {
        static::assertValidStatusCode($code);
        if (! isset(self::$errorPhrases[$code])) {
            throw new \OutOfBoundsException(\sprintf('Unknown http status code: `%s`.', $code));
        }

        return self::$errorPhrases[$code];
    }

    /**
     * Get the name for a given status code.
     *
     * @param int $code http status code
     *
     * @throws \InvalidArgumentException If the requested $code is not valid
     * @throws \OutOfBoundsException     If the requested $code is not found
     *
     * @return string Returns name for the given status code
     */
    public static function getReasonPhrase(int $code): string
    {
        static::assertValidStatusCode($code);
        if (! isset(self::$statusNames[$code])) {
            throw new \OutOfBoundsException(\sprintf('Unknown http status code: `%s`.', $code));
        }

        return self::$statusNames[$code];
    }

    /**
     * Get the text for a given status code.
     *
     * @param int $code http status code
     *
     * @return string Returns name for the given Exception corresponding to the status code
     */
    // TODO : on devrait peut-être directement renvoyer une instance de 'new $ExceptionName()', et eventuellement faire un "new HttpException($code)", et si on n'a pas trouvé l'exception dans la liste des noms (ca ferait une initialisation par défaut de l'exception en utilisant la classe de base !!!!)
    public static function getExceptionNameByStatusCode(int $code): string
    {
        static::assertValidStatusCode($code, 400);
        if (! isset(self::$mapExceptionName[$code])) {
            throw new \OutOfBoundsException(\sprintf('Unknown http status code: `%s`.', $code));
        }

        return self::$mapExceptionName[$code];
    }

    /**
     * Filter a HTTP Status code.
     *
     * @param int $code
     * @param int $min
     * @param int $max
     *
     * @throws \InvalidArgumentException if the HTTP status code is invalid
     *
     * @return int
     */
    // TODO : renommer cette méthode en assertXXXX() et virer l'ancien code pour ne pas retourner un int mais seulement lever une exception si le test est KO !!!!
    public static function assertValidStatusCode(int $code, int $min = self::MIN_STATUS_CODE_VALUE, int $max = self::MAX_STATUS_CODE_VALUE): void
    {
        if ($code < $min || $code > $max) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid status code "%s"; must be an integer between %d and %d, inclusive.',
                $code,
                $min,
                $max
            ));
        }
    }
}
