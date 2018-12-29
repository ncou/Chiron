<?php

declare(strict_types=1);

namespace Chiron\Handler\Formatter;

use Chiron\Handler\ExceptionInfo;
use Chiron\Http\Exception\HttpException;
use Throwable;

class JsonFormatter implements FormatterInterface
{
    /**
     * The exception info instance.
     *
     * @var \Chiron\Handler\ExceptionInfo
     */
    protected $info;

    /**
     * Pretty format the output xml ?
     *
     * @var bool
     */
    // TODO : initialiser cette valeur via un parametre dans le constructeur.
    // TODO : renommer en isPretty
    protected $pretty = true;

    /**
     * Create a new json displayer instance.
     *
     * @param \Chiron\Handler\ExceptionInfo $info
     */
    // TODO : permettre de passer en paramétre le json flags ($jsonEncodeOptions) ????
    // TODO : passer en paramétre la valeur du isPretty == true par défaut
    public function __construct(ExceptionInfo $info)
    {
        $this->info = $info;
    }

    /**
     * Render JSON error.
     *
     * @param \Throwable $e
     *
     * @return string
     */
    public function format(Throwable $e): string
    {
        $info = $this->info->generate($e);

        $error = ['status' => $info['code'], 'title' => $info['name'], 'detail' => $info['detail']];

        return $this->toJson($error);
    }

    private function toJson(array $data)
    {
        $jsonEncodeOptions = JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
            | JSON_PRESERVE_ZERO_FRACTION; // | JSON_PARTIAL_OUTPUT_ON_ERROR

        if ($this->pretty) {
            $jsonEncodeOptions |= JSON_PRETTY_PRINT;
        }

        $json = json_encode($data, $jsonEncodeOptions);

        if ($json === false) {
            $this->throwEncodeError(json_last_error(), $data);
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
        return 'application/problem+json';
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
     * Throws an exception according to a given code with a customized message
     *
     * @param  int               $code return code of json_last_error function
     * @param  mixed             $data data that was meant to be encoded
     * @throws \RuntimeException
     */
    private function throwEncodeError(int $code, $data)
    {
        switch ($code) {
            case JSON_ERROR_DEPTH:
                $msg = 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $msg = 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $msg = 'Unexpected control character found';
                break;
            case JSON_ERROR_UTF8:
                $msg = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $msg = 'Unknown error';
        }
        throw new \RuntimeException('JSON encoding failed: '.$msg.'. Encoding: '.var_export($data, true));
    }
}
