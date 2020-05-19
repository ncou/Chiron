<?php

declare(strict_types=1);

namespace Chiron\ErrorHandler;

use Chiron\Console\Console;
use Chiron\Container\Container;
//use Chiron\Http\Psr\Response;
use Chiron\ErrorHandler\Exception\FatalErrorException;
use ErrorException;
use Exception;
use Symfony\Component\Console\Output\ConsoleOutput;
use Throwable;

//https://github.com/cakephp/cakephp/blob/master/src/Error/BaseErrorHandler.php#L89
//https://github.com/cakephp/cakephp/blob/master/src/Error/ConsoleErrorHandler.php
//https://github.com/cakephp/cakephp/blob/master/src/Error/ErrorHandler.php#L205
//https://github.com/cakephp/cakephp/blob/master/src/Error/Middleware/ErrorHandlerMiddleware.php

//https://github.com/laravel/lumen-framework/blob/38f42c1399650b6c2286de7e9831e7174cfd14e8/src/Concerns/RegistersExceptionHandlers.php
//https://github.com/laravel/lumen-framework/blob/38f42c1399650b6c2286de7e9831e7174cfd14e8/src/Application.php#L103

// TODO : regarder ici comment tester la méthode shutdownHandler !!!!     https://github.com/nette/tracy/blob/02b60e183ad82c26ad8415547ab393941bef7e94/tests/Tracy/Debugger.E_COMPILE_ERROR.console.phpt

//https://github.com/getsentry/sentry-php/blob/master/src/ErrorHandler.php
//https://github.com/yiisoft/yii2/blob/1a8c83ba438f92075fc6e4ab9124b6ae59fdda8f/framework/web/ErrorHandler.php

//https://github.com/yiisoft/yii-web/blob/master/src/ErrorHandler/ErrorHandler.php

final class RegisterErrorHandler
{
    /**
     * Register this error handler.
     */
    public static function register(): void
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 'Off');

        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Convert PHP errors to ErrorException instances.
     *
     * @param int    $level
     * @param string $message
     * @param string $file
     * @param int    $line
     *
     * @throws \ErrorException
     */
    public static function handleError(int $level, string $message, string $file = '', int $line = 0): void
    {
        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Handle an uncaught exception from the application.
     *
     * @param \Throwable $e
     */
    public static function handleException(Throwable $e): void
    {
        // disable error capturing to avoid recursive errors while handling exceptions
        //$this->unregister();

        try {
            //$this->getExceptionHandler()->report($e);
        } catch (Exception $e) {
            //
        }

        if (php_sapi_name() === 'cli') {
            self::renderForConsole($e);
        } else {
            self::renderHttpResponse($e);
        }
    }

    /**
     * Render an exception to the console.
     *
     * @param \Throwable $e
     */
    private static function renderForConsole(Throwable $e): void
    {
        $console = new Console(Container::$instance);
        $console->renderThrowable($e, new ConsoleOutput());
    }

    /**
     * Render an exception as an HTTP response and send it.
     *
     * @param \Throwable $e
     */
    // TODO : code à améliorer !!!!!!
    // TODO : regarder ici comment c'est fait (initialiser un SapiEmitter::class) :    https://github.com/cakephp/cakephp/blob/master/src/Error/ErrorHandler.php#L205
    private static function renderHttpResponse(Throwable $e): void
    {
        // TODO : externaliser la création du content dans une méthode séparée du style '$this->handleCaughtThrowable($throwable): string' qui retourne le texte à la méthode echo. Elle pourrait être aussi utilisée dans le middleware de ErroHandlerMiddleware pour créer le contenu de la réponse !!!!
        $content = '';

        try {
            //$this->log($t);
            //return $this->exposeDetails ? $renderer->renderVerbose($t) : $renderer->render($t);

            $formatter = new \Chiron\ErrorHandler\Formatter\WhoopsFormatter();
            $content = $formatter->format2($e);
        } catch (\Throwable $t) {
            $content = nl2br($t->getMessage());
        }

        // set preventive HTTP status code to 500 in case error handling somehow fails and headers are sent
        http_response_code(500);

        echo $content;
        exit(1);
    }

    /**
     * Handle php shutdown and search for fatal errors.
     *
     *
     * @throws FatalErrorException
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error !== null && self::isFatalError($error['type'])) {
            $exception = new FatalErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            );

            $this->handleException($exception);
        }
    }

    /**
     * Determine if the error type is fatal.
     *
     * @param int $type
     *
     * @return bool
     */
    private static function isFatalError(int $type): bool
    {
        return in_array($type, [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING]);
    }
}
