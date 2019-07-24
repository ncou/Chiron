<?php

declare(strict_types=1);

namespace Chiron\Handler\Formatter;

use Chiron\Http\Exception\HttpException;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

//https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/JsonFormatter.php
//https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/NormalizerFormatter.php

// TODO : Constructeur => permettre de passer en paramétre le json flags ($jsonEncodeOptions) ????
// TODO : Constructeur => passer en paramétre la valeur du isPretty == true par défaut
class JsonFormatter implements FormatterInterface
{
    /**
     * Pretty format the output xml ?
     *
     * @var bool
     */
    // TODO : initialiser cette valeur via un parametre dans le constructeur.
    // TODO : renommer en isPretty
    protected $pretty = true;

    /**
     * Render JSON error.
     *
     * @param \Throwable $e
     *
     * @return string
     */
    public function format(ServerRequestInterface $request, Throwable $e): string
    {
        // This class doesn't show debug information, so by default we hide the php exception behind a neutral http 500 error.
        if (! $e instanceof HttpException) {
            $e = new \Chiron\Http\Exception\Server\InternalServerErrorHttpException();
        }

        return $this->arrayToJson($e->toArray());
    }

    private function arrayToJson(array $data): string
    {
        // TODO : permettre de configurer cette option soit directement dans le constructeur, soit en créant une méthode setFlagOptions($flag)
        $jsonEncodeOptions = JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_LINE_TERMINATORS
            | JSON_PRESERVE_ZERO_FRACTION; // | JSON_PARTIAL_OUTPUT_ON_ERROR

        if ($this->pretty) {
            $jsonEncodeOptions |= JSON_PRETTY_PRINT;
        }// else {
        //$jsonEncodeOptions ^= JSON_PRETTY_PRINT;
        //}

        $json = json_encode($data, $jsonEncodeOptions);

        if ($json === false) {
            throw new InvalidArgumentException('JSON encoding failed: ' . json_last_error_msg());
        }

        return $json;
    }

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
}
