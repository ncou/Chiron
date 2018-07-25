<?php

declare(strict_types=1);

namespace Chiron\Handler\Error;

use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;


use Jgut\HttpException\ForbiddenHttpException;
use Jgut\HttpException\HttpException;
use Jgut\HttpException\InternalServerErrorHttpException;
use Jgut\HttpException\MethodNotAllowedHttpException;
use Jgut\HttpException\NotFoundHttpException;
use Jgut\HttpException\UnauthorizedHttpException;
use Jgut\Slim\Exception\Whoops\Formatter\Text;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;
use Chiron\Http\Psr\Response;

use Chiron\Http\StatusCode;




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

class ExceptionHelper
{
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
     * Get the text for a given status code.
     *
     * @param int $code http status code
     *
     * @return string Returns name for the given Exception corresponding to the status code
     */
    public static function getExceptionNameByStatusCode(int $code): string
    {
        if ($code < 400 || $code > 599) {
            throw new \InvalidArgumentException("Invalid status code '$code'; must be an integer between 400 and 599, inclusive.");
        }

        if (! isset(self::$mapExceptionName[$code])) {
            throw new \OutOfBoundsException(\sprintf('Unknown http status code: `%s`.', $code));
        }

        return self::$mapExceptionName[$code];
    }

}
