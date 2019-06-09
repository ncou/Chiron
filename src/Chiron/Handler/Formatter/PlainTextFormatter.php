<?php

declare(strict_types=1);

namespace Chiron\Handler\Formatter;

use Chiron\Http\Exception\HttpException;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use function trim;

class PlainTextFormatter implements FormatterInterface
{
    /**
     * Render Plain-Text error.
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

        return trim($this->arrayToPlainText($e->toArray()));
    }

    /**
     * @param array  $array
     * @param string $title
     *
     * @return string
     */
    // TODO : améliorer cette méthode avec ce bout de code : https://github.com/cakephp/cakephp/blob/dc63c2f0d8a1e9d5f336ab81b587a54929d9e1cf/src/Error/Debugger.php#L508
    private function arrayToPlainText(array $array, $title = null): string
    {
        $root = 'error';
        $text = '';

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if ($title !== null) {
                    $key = $title . '.' . $key;
                }
                $text .= $this->arrayToPlainText($value, $key);
            } else {
                if (is_null($value) || is_object($value)) {
                    $value = json_encode($value);
                }
                if (is_bool($value)) {
                    $value = ($value) ? 'true' : 'false';
                }
                if ($title != '') {
                    $text .= $root . '.' . $title . '.' . $key . ': ' . $value . PHP_EOL;
                } else {
                    $text .= $root . '.' . $key . ': ' . $value . PHP_EOL;
                }
            }
        }

        return $text;
    }

    /**
     * Get the supported content type.
     *
     * @return string
     */
    public function contentType(): string
    {
        return 'text/plain';
    }

    /**
     * Do we provide verbose information about the exception?
     *
     * @return bool
     */
    public function isVerbose(): bool
    {
        // TODO : conditionner l'affichage de la stackstrace avec la valeur de ce booléen (qui représente le debug = true ou false).
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
