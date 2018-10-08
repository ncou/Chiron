<?php

declare(strict_types=1);

namespace Chiron\Exception;

use Chiron\Http\Exception\HttpException;
use Throwable;

//Autre example :     https://github.com/otherguy/laravel-error-handler/blob/master/src/Classes/PlainDisplay.php#L114
//avec l'équivalent au fichier errors.json      https://github.com/otherguy/laravel-error-handler/blob/master/resources/lang/en/messages.php

/**
 * Class to retrieve the Exception information (description...etc).
 */
class ExceptionInfo
{
    /**
     * The json error description file info path.
     *
     * @var string|null
     */
    protected $path;

    /**
     * Create a exception info instance.
     *
     * @param string|null $path the .json file path for the exception informations
     */
    public function __construct(string $path = null)
    {
        $this->path = $path;
    }

    /**
     * Get the exception information.
     *
     * @param \Throwable $exception
     * @param int        $code
     *
     * @return array
     */
    public function generate(Throwable $exception, int $code)
    {
        $errors = $this->path ? json_decode(file_get_contents($this->path), true) : [500 => ['name' => 'Internal Server Error', 'message' => 'An error has occurred and this resource cannot be displayed.']];

        if (! isset($errors[$code])) {
            $code = 500;
        }

        $info = array_merge(['code' => $code], $errors[$code]);

        if ($exception instanceof HttpException) {
            $msg = (string) $exception->getMessage();
            // TODO : regarder l'utilité de la vérification sur la longueur de 4 ou 36 caractéres.
            $info['detail'] = ! empty($msg) && strlen($msg) > strlen($info['message']) ? $msg : $info['message'];
        } else {
            $info['detail'] = $info['message'];
        }
        // the 'message' value is not used in the final array (it's used before to conditionaly populate the 'detail' value) so we remove it.
        unset($info['message']);

        /*
                if ($exception instanceof HttpException) {
                    $msg = (string) $exception->getMessage();
                    // TODO : regarder l'utilité de la vérification sur la longueur de 4 ou 36 caractéres.
                    $info['detail'] = (strlen($msg) > 4) ? $msg : $info['message'];
                    $info['summary'] = (strlen($msg) < 36 && strlen($msg) > 4) ? $msg : 'Houston, We Have A Problem.';
                } else {
                    $info['detail'] = $info['message'];
                    $info['summary'] = 'Houston, We Have A Problem.';
                }
                // the 'message' value is not used in the final array (it's used before to conditionaly populate the 'detail' value) so we remove it.
                unset($info['message']);
        */

        return $info;
    }
}
