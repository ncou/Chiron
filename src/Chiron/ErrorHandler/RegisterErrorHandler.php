<?php

declare(strict_types=1);

namespace Chiron\ErrorHandler;

use Chiron\Container\Container;
use Chiron\ErrorHandler\Formatter\FormatterInterface;
use Chiron\ErrorHandler\Formatter\PlainTextFormatter;
use Chiron\Http\Exception\HttpException;
//use Chiron\Http\Psr\Response;
use Exception;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

use Symfony\Component\Console\Output\ConsoleOutput;
use Chiron\Console\Console;
use ErrorException;

//https://github.com/laravel/lumen-framework/blob/38f42c1399650b6c2286de7e9831e7174cfd14e8/src/Concerns/RegistersExceptionHandlers.php
//https://github.com/laravel/lumen-framework/blob/38f42c1399650b6c2286de7e9831e7174cfd14e8/src/Application.php#L103

// TODO : regarder ici comment tester la méthode shutdownHandler !!!!     https://github.com/nette/tracy/blob/02b60e183ad82c26ad8415547ab393941bef7e94/tests/Tracy/Debugger.E_COMPILE_ERROR.console.phpt

//https://github.com/getsentry/sentry-php/blob/master/src/ErrorHandler.php
//https://github.com/yiisoft/yii2/blob/1a8c83ba438f92075fc6e4ab9124b6ae59fdda8f/framework/web/ErrorHandler.php

//https://github.com/yiisoft/yii-web/blob/master/src/ErrorHandler/ErrorHandler.php

final class RegisterErrorHandler
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    // TODO : passer les méthodes en static !!!
    public function register()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 'Off');

        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * Unregisters this error handler by restoring the PHP error and exception handlers.
     */
    public function unregister(): void
    {
        restore_error_handler();
        restore_exception_handler();
    }

    /**
     * Convert PHP errors to ErrorException instances.
     *
     * @param  int  $level
     * @param  string  $message
     * @param  string  $file
     * @param  int  $line
     * @param  array  $context
     * @return void
     *
     * @throws \ErrorException
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = [])
    {
        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Handle an uncaught exception from the application.
     *
     * Note: Most exceptions can be handled via the try / catch block in
     * the HTTP and Console kernels. But, fatal error exceptions must
     * be handled differently since they are not normal exceptions.
     *
     * @param  \Throwable  $e
     * @return void
     */
    public function handleException(Throwable $e)
    {
        // disable error capturing to avoid recursive errors while handling exceptions
        //$this->unregister();

        try {
            //$this->getExceptionHandler()->report($e);
        } catch (Exception $e) {
            //
        }

        //if ($this->runningInConsole()) {
        if (PHP_SAPI === 'cli') {
            $this->renderForConsole($e);
        } else {
            $this->renderHttpResponse($e);
        }
    }

    /**
     * Render an exception to the console.
     *
     * @param  \Throwable  $e
     * @return void
     */
    protected function renderForConsole(Throwable $e)
    {
        (new Console(Container::$instance))->renderThrowable($e, new ConsoleOutput());

        //$this->getExceptionHandler()->renderForConsole(new ConsoleOutput, $e);
    }

    /**
     * Render an exception as an HTTP response and send it.
     *
     * @param  \Throwable  $e
     * @return void
     */
    // TODO : code à améliorer !!!!!!
    protected function renderHttpResponse(Throwable $e)
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
     * Handle the PHP shutdown event.
     *
     * @return void
     */
    public function handleShutdown()
    {
        if (! is_null($error = error_get_last()) && $this->isFatalError($error['type'])) {
            // TODO : code à corriger car cela ne fonctionnera pas !!!!!! exemple qui fonctionne : https://github.com/yiisoft/yii-web/blob/master/src/ErrorHandler/ErrorHandler.php#L125
            $this->handleException($this->fatalErrorFromPhpError($error, 0));
        }
    }

    /**
     * Create a new fatal error instance from an error array.
     *
     * @param  array  $error
     * @param  int|null  $traceOffset
     * @return \Symfony\Component\ErrorHandler\Error\FatalError
     */
    protected function fatalErrorFromPhpError(array $error, $traceOffset = null)
    {
        return new FatalError($error['message'], 0, $error, $traceOffset);
    }

    /**
     * Determine if the error type is fatal.
     *
     * @param  int  $type
     * @return bool
     */
    // TODO : exemple : https://github.com/yiisoft/yii-web/blob/master/src/ErrorHandler/ErrorException.php#L42
    protected function isFatalError(int $type): bool
    {
        return in_array($type, [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE]);
        //E_ERROR | E_USER_ERROR | E_COMPILE_ERROR | E_CORE_ERROR | E_PARSE;
        //[E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING]
    }

    /**
     * Get an instance of the exception handler.
     *
     * @return \Illuminate\Contracts\Debug\ExceptionHandler
     */
    /*
    protected function getExceptionHandler()
    {
        return $this->app->make(ExceptionHandler::class);
    }*/

}
