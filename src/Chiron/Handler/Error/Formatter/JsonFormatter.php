<?php

declare(strict_types=1);

namespace Chiron\Handler\Error\Formatter;

use Chiron\Http\Exception\HttpException;
use ErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use UnexpectedValueException;

//https://github.com/userfrosting/UserFrosting/blob/master/app/sprinkles/core/src/Error/Renderer/JsonRenderer.php

class JsonFormatter implements ExceptionFormatterInterface
{
    public function formatException(Throwable $exception, bool $displayErrorDetails): string
    {
        return $this->renderJsonBody($exception, $displayErrorDetails);
    }

    /**
     * Render JSON error.
     *
     * @param Throwable $error
     *
     * @return string
     */
    private function renderJsonBody(Throwable $error, bool $displayErrorDetails): string
    {
        $json = [
            'message' => 'Chiron Application Error',
        ];
        if ($displayErrorDetails) {
            $json['error'] = [];
            do {
                $json['error'][] = [
                    'type'    => get_class($error),
                    'code'    => $error->getCode(),
                    'message' => $error->getMessage(),
                    'file'    => $this->replaceRoot($error->getFile()),
                    'line'    => $error->getLine(),
                    // TODO : réfléchir si on affiche la trace.
                    'trace'   => explode("\n", $error->getTraceAsString()),
                ];
            } while ($error = $error->getPrevious());
        }
        return json_encode($json, JSON_PRETTY_PRINT); //JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    }

    /**
     * replaceRoot.
     *
     * @param string $file
     *
     * @return string
     */
    protected function replaceRoot(string $file): string
    {
        if (defined('Chiron\ROOT_DIR')) {
            $file = 'ROOT' . substr($file, strlen(\Chiron\ROOT_DIR));
        }

        return $file;
    }
}
