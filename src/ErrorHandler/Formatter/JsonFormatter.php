<?php

declare(strict_types=1);

namespace Chiron\ErrorHandler\Formatter;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

//https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/JsonFormatter.php
//https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/NormalizerFormatter.php

//https://github.com/thephpleague/booboo/blob/5f2d93a329df9ccb9edf77dac83c483829893d2e/src/Formatter/JsonFormatter.php

//https://github.com/lcobucci/content-negotiation-middleware/blob/master/src/Formatter/Json.php

// FORMAT STACKTRACE :
//*********************
//https://github.com/nekonomokochan/php-json-logger/blob/master/src/PhpJsonLogger/ErrorsContextFormatter.php#L31
//https://github.com/nuxsmin/sysPass/blob/master/lib/BaseFunctions.php#L107

// TODO : Constructeur => permettre de passer en paramétre le json flags ($jsonEncodeOptions) ????
// TODO : Constructeur => passer en paramétre la valeur du isPretty == true par défaut
class JsonFormatter extends AbstractFormatter
{
    //json_encode(['errors' => [$error]], \JSON_HEX_TAG | \JSON_HEX_APOS | \JSON_HEX_AMP | \JSON_HEX_QUOT | \JSON_UNESCAPED_SLASHES)
    private const DEFAULT_JSON_FLAGS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;

    /**
     * Get the supported content type.
     *
     * @return string
     */
    public function contentType(): string
    {
        return 'application/json';
    }

    /**
     * Do we provide verbose information about the exception?
     *
     * @return bool
     */
    public function isVerbose(): bool
    {
        return false;
    }

    /**
     * Can we format the exception?
     *
     * @param \Throwable $e
     *
     * @return bool
     */
    public function canFormat(Throwable $e): bool
    {
        return true;
    }

    /**
     * Render JSON error.
     *
     * @param \Throwable $e
     *
     * @return string
     */
    public function format(ServerRequestInterface $request, Throwable $e): string
    {
        $data['status'] = $this->getErrorStatusCode($e);
        $data['title'] = $this->getErrorTitle($e);
        $data['detail'] = $this->getErrorDetail($e);

        $json = json_encode($data, self::DEFAULT_JSON_FLAGS);

        if ($json === false) {
            throw new InvalidArgumentException('JSON encoding failed: ' . json_last_error_msg() . '. Encoding: ' . var_export($data, true));
        }

        return $json;
    }
}
