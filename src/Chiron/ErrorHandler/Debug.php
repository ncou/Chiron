<?php

declare(strict_types=1);

namespace Chiron\ErrorHandler;

//use Chiron\Http\Psr\Response;
use ErrorException;
use Exception;
use ReflectionProperty;
use Throwable;

//https://codereview.stackexchange.com/questions/145239/prettifying-phps-exception-stacktraces

//https://github.com/ventoviro/windwalker-core/blob/e70121c1767c58e9fa22c4f50261084502cf870a/src/Core/Utilities/Debug/BacktraceHelper.php

// TODO : améliroer les messages des exceptions => https://github.com/nette/tracy/blob/ca52715e9771822fb5d49386cc85fda6d2b83ed0/src/Tracy/Helpers.php#L175

// TODO : ajouter des tests pour la fonction "call()"   =>  https://github.com/symfony/symfony/blob/master/src/Symfony/Component/ErrorHandler/Tests/ErrorHandlerTest.php

// TODO : example : https://github.com/bolt/common/blob/2.0/src/Thrower.php
// TODO : classe à renommer en "Debugger" ???
final class Debug
{
    /**
     * Calls a function and turns any PHP error into \ErrorException.
     *
     * @throws \ErrorException When $function(...$arguments) triggers a PHP error
     *
     * @return mixed What $function(...$arguments) returns
     */
    public static function call(callable $function, ...$arguments)
    {
        //error_reporting(E_ALL);
        set_error_handler(self::createErrorHandler());

        try {
            return $function(...$arguments);
        } finally {
            restore_error_handler();
        }
    }

    /**
     * Creates and returns a callable error handler that raises exceptions.
     *
     * Only raises exceptions for errors that are within the error_reporting mask.
     *
     * Fatal errors normally do not provide any trace making it harder to debug. In case XDebug is installed, we
     * can get a trace using xdebug_get_function_stack().
     *
     * @return callable
     */
    // TODO : améliorer avec le code suivant : https://github.com/laravel/framework/blob/master/src/Illuminate/Foundation/Bootstrap/HandleExceptions.php#L57
    // https://github.com/yiisoft/yii2/blob/master/framework/base/ErrorHandler.php#L205
    //https://github.com/samsonasik/ErrorHeroModule/blob/6af28a0520257caf9e8e66d5aa35819c2e4327cf/src/HeroTrait.php#L81
    //https://github.com/yiisoft/yii-web/blob/74a9a0e2aa8c6dcfa19c4620b787262a558b38cf/src/ErrorHandler/ErrorHandler.php#L43
    //https://github.com/symfony/symfony/blob/700d2d39ca8b14fada8458c3fb54c70bfbcd042b/src/Symfony/Component/ErrorHandler/ErrorHandler.php#L399
    private static function createErrorHandler(): callable
    {
        /*
         * @param int $severity the level of the error raised.
         * @param string $message the error message.
         * @param string $file the filename that the error was raised in.
         * @param int $line the line number the error was raised at.
         * @return void
         * @throws ErrorException if error is not within the error_reporting mask.
         */
        return function (int $severity, string $message, string $file, int $line): void {
            // the error code should be in the error_reporting range
            //if (error_reporting() & $severity) {

            /*
                            if (__FILE__ === $file) {
                                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
                                $file = $trace[2]['file'] ?? $file;
                                $line = $trace[2]['line'] ?? $line;
                            }
            */
            $exception = new ErrorException($message, 0, $severity, $file, $line);

            // overwrite the "trace" property if the xdebug extension is enabled.
            if (function_exists('xdebug_get_function_stack')) {
                $stack = [];

                // remove the useless last frame in the stack.
                foreach (array_slice(array_reverse(xdebug_get_function_stack()), 0, -1) as $row) {
                    $frame = [
                        'file'     => $row['file'],
                        'line'     => $row['line'],
                        'function' => $row['function'] ?? '*unknown*',
                        'args'     => [],
                    ];

                    if (! empty($row['class'])) {
                        $frame['type'] = isset($row['type']) && $row['type'] === 'dynamic' ? '->' : '::';
                        $frame['class'] = $row['class'];
                    }

                    $stack[] = $frame;
                }

                $ref = new ReflectionProperty(Exception::class, 'trace');
                $ref->setAccessible(true);
                $ref->setValue($exception, $stack);
            }

            throw $exception;
            //}
        };
    }

    // TODO : méthode à virer !!!
    public static function disableDisplayErrors(): void
    {
        if (function_exists('ini_set')) {
            ini_set('display_errors', '0');
        }

        /*
        error_reporting(-1);
        if (!\in_array(\PHP_SAPI, ['cli', 'phpdbg'], true)) {
            ini_set('display_errors', 0);
        } elseif (!filter_var(ini_get('log_errors'), FILTER_VALIDATE_BOOLEAN) || ini_get('error_log')) {
            // CLI - display errors only if they're not already logged to STDERR
            ini_set('display_errors', 1);
        }
        */
    }

    /**
     * Create plain text exception representation and return it as a string.
     *
     * @param Throwable $e
     *
     * @return string
     */
    // TODO : formatter aussi la previousException qui est portée dans l'exception d'entrée
    public static function formatException(Throwable $e): string
    {
        // replace invisible ascii characters (range 0-9 and 11-31 except the new line character 10) with a single space character.
        //$message = preg_replace('#[\x00-\x09\x0B-\x1F]+#', ' ', $e->getMessage());

        $class = $e instanceof ErrorException ? self::translateErrorCode($e->getSeverity()) : self::getClass($e);

        // replace invisible ascii characters (range 0-9 and 11-31 except the new line character 10) with a single space character.
        //$message = preg_replace('#[\x00-\x09\x0B-\x1F]+#', ' ', $e->getMessage());

        return sprintf(
            "%s (code: %d) thrown with message '%s' in file %s on line %d\n[stacktrace]\n%s\n",
            $class,
            $e->getCode(),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        /*
        // TODO : permettre d'afficher ou non la stacktrace selon un paramétre booléen de la méthode
                if ($includeStacktraces) {
                    $str .= "[stacktrace]\n".$e->getTraceAsString()."\n";
                }*/
    }

    /**
     * Translate ErrorException code into the represented constant.
     *
     * @param int $error_code
     *
     * @return string
     */
    // TODO : renommer la fonction en errorCodeToString() ????
    public static function translateErrorCode(int $error_code): string
    {
        $consts = get_defined_constants(true);
        foreach ($consts['Core'] as $constant => $value) {
            if (substr($constant, 0, 2) === 'E_' && $value === $error_code) {
                return $constant;
            }
        }

        return 'E_UNKNOWN';
    }

    /*
        public static function codeToString($code): string
        {
            switch ($code) {
                case E_ERROR:
                    return 'E_ERROR';
                case E_WARNING:
                    return 'E_WARNING';
                case E_PARSE:
                    return 'E_PARSE';
                case E_NOTICE:
                    return 'E_NOTICE';
                case E_CORE_ERROR:
                    return 'E_CORE_ERROR';
                case E_CORE_WARNING:
                    return 'E_CORE_WARNING';
                case E_COMPILE_ERROR:
                    return 'E_COMPILE_ERROR';
                case E_COMPILE_WARNING:
                    return 'E_COMPILE_WARNING';
                case E_USER_ERROR:
                    return 'E_USER_ERROR';
                case E_USER_WARNING:
                    return 'E_USER_WARNING';
                case E_USER_NOTICE:
                    return 'E_USER_NOTICE';
                case E_STRICT:
                    return 'E_STRICT';
                case E_RECOVERABLE_ERROR:
                    return 'E_RECOVERABLE_ERROR';
                case E_DEPRECATED:
                    return 'E_DEPRECATED';
                case E_USER_DEPRECATED:
                    return 'E_USER_DEPRECATED';
            }

            return 'Unknown PHP error';
        }
    */

    //https://github.com/Seldaek/monolog/blob/f9d56fd2f5533322caccdfcddbb56aedd622ef1c/src/Monolog/Utils.php#L21
    public static function getClass($object): string
    {
        $class = get_class($object);

        return 'c' === $class[0] && 0 === strpos($class, "class@anonymous\0") ? get_parent_class($class) . '@anonymous' : $class;
    }

    /**
     * Parse the error message by removing the anonymous class notation
     * and using the parent class instead if possible.
     */
    // TODO : méthode et test à virer !!!!
    public static function parseAnonymousClass(string $message): string
    {
        if (false !== strpos($message, "class@anonymous\0")) {
            $message = preg_replace_callback('/class@anonymous\x00.*?\.php(?:0x?|:)[0-9a-fA-F]++/', function ($m) {
                return class_exists($m[0], false) ? get_parent_class($m[0]) . '@anonymous' : $m[0];
            }, $message);
        }

        return $message;
    }

    /**
     * Starts/stops stopwatch.
     *
     * @return float elapsed seconds
     */
    public static function timer(string $name = null): float
    {
        static $time = [];
        $now = microtime(true);
        $delta = isset($time[$name]) ? $now - $time[$name] : 0;
        $time[$name] = $now;

        return $delta;
    }

    /**
     * Detects debug mode by IP address.
     *
     * @param string|array $list IP addresses or computer names whitelist detection
     */
    public static function detectDebugMode($list = null): bool
    {
        $addr = $_SERVER['REMOTE_ADDR'] ?? php_uname('n');
        $secret = isset($_COOKIE[self::COOKIE_SECRET]) && is_string($_COOKIE[self::COOKIE_SECRET])
            ? $_COOKIE[self::COOKIE_SECRET]
            : null;
        $list = is_string($list)
            ? preg_split('#[,\s]+#', $list)
            : (array) $list;
        if (! isset($_SERVER['HTTP_X_FORWARDED_FOR']) && ! isset($_SERVER['HTTP_FORWARDED'])) {
            $list[] = '127.0.0.1';
            $list[] = '::1';
            $list[] = '[::1]'; // workaround for PHP < 7.3.4
        }

        return in_array($addr, $list, true) || in_array("$secret@$addr", $list, true);
    }

    /**
     * @suppress PhanUnreferencedPublicMethod
     */
    //https://github.com/phan/phan/blob/master/src/Phan/Debug.php#L237
    public static function backtrace(int $levels = 0): void
    {
        $bt = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, $levels + 1);
        foreach ($bt as $level => $context) {
            if (! $level) {
                continue;
            }
            $file = $context['file'] ?? 'unknown';
            $line = $context['line'] ?? 1;
            $class = $context['class'] ?? 'global';
            $function = $context['function'] ?? '';

            echo '#' . ($level - 1) . " $file:$line $class ";
            if (isset($context['type'])) {
                echo $context['class'] . $context['type'];
            }
            echo $function;
            echo "\n";
        }
    }

    /**
     * Print a message with the file and line.
     *
     * @suppress PhanUnreferencedPublicMethod added for debugging
     */
    //https://github.com/phan/phan/blob/master/src/Phan/Debug.php#L465
    public static function debugLog(string $message): void
    {
        $frame = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS)[0];
        \fprintf(\STDERR, "%s:%d %s\n", $frame['file'] ?? 'unknown', $frame['line'] ?? 0, $message);
    }

    /**
     * Returns the last PHP error as plain string.
     */
    // TODO : méthode à virer elle ne sert à rien. Il faudra aussi virer les tests.
    public static function getLastError(): string
    {
        $message = error_get_last()['message'] ?? '';
        $message = ini_get('html_errors') ? static::htmlToText($message) : $message;
        $message = preg_replace('#^\w+\(.*?\): #', '', $message);

        return $message;
    }

    /**
     * Converts given HTML code to plain text.
     */
    private static function htmlToText(string $html): string
    {
        return html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /*
    public function getMessage(\Throwable $e): string
    {
        return sprintf('%s: %s in %s at line %s', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine());
    }*/

    /**
     * Normalized exception stacktrace.
     *
     * @param \Throwable $e
     *
     * @return array
     */
    /*
    protected function getStacktrace(\Throwable $e): array
    {
        $stacktrace = $e->getTrace();
        if (empty($stacktrace)) {
            return [];
        }

        //Let's let's clarify exception location
        $header = [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ] + $stacktrace[0];

        if ($stacktrace[0] != $header) {
            array_unshift($stacktrace, $header);
        }

        return $stacktrace;
    }*/

// Polyfill PHP8.0
    /*
    public static function get_debug_type($value): string
    {
        switch (true) {
            case null === $value: return 'null';
            case \is_bool($value): return 'bool';
            case \is_string($value): return 'string';
            case \is_array($value): return 'array';
            case \is_int($value): return 'int';
            case \is_float($value): return 'float';
            case \is_object($value): break;
            case $value instanceof \__PHP_Incomplete_Class: return '__PHP_Incomplete_Class';
            default:
                if (null === $type = @get_resource_type($value)) {
                    return 'unknown';
                }

                if ('Unknown' === $type) {
                    $type = 'closed';
                }

                return "resource ($type)";
        }

        $class = \get_class($value);

        if (false === strpos($class, '@')) {
            return $class;
        }

        return (get_parent_class($class) ?: key(class_implements($class)) ?: 'class').'@anonymous';
    }
    */
}
