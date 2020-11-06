<?php

declare(strict_types=1);

namespace Chiron\ErrorHandler\Exception;

//https://github.com/Kdyby/Console/blob/master/src/FatalThrowableError.php#L48
//https://github.com/symfony/debug/blob/4.4/Exception/FatalThrowableError.php#L48
//https://github.com/symfony/debug/blob/4.4/Exception/FatalErrorException.php#L75
//https://github.com/symfony/error-handler/blob/master/Error/FatalError.php#L70
//https://github.com/yiisoft/yii2-framework/blob/master/base/ErrorException.php#L47
//https://github.com/nette/tracy/blob/master/src/Tracy/Helpers.php#L103

/**
 * Raised on fatal exceptions.
 */
class FatalErrorException extends \ErrorException
{
    /**
     * Display an empty stacktrace if there is not xdebug extension.
     * Display xdebug stacktrace (minus 2 frames to avoid displaying handleShutdown function and FatalErrorException constructor).
     */
    public function __construct($message = '', $code = 0, $severity = 1, $filename = __FILE__, $lineno = __LINE__, \Exception $previous = null)
    {
        parent::__construct($message, $code, $severity, $filename, $lineno, $previous);

        $trace = [];

        if (extension_loaded('xdebug') && xdebug_is_enabled()) {
            $stack = xdebug_get_function_stack();
            array_splice($stack, -2);

            foreach ($stack as $row) {
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

                $trace[] = $frame;
            }

            $trace = array_reverse($trace);
        }

        // override the stacktrace (with an empty trace array, or an xdebug stacktrace array).
        $this->setTrace($trace);
    }

    private function setTrace(array $trace): void
    {
        $traceReflector = new \ReflectionProperty('Exception', 'trace');
        $traceReflector->setAccessible(true);
        $traceReflector->setValue($this, $trace);
    }
}
