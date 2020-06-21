<?php
/**
 * @see http://www.yiiframework.com/
 *
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Chiron\Support;

use Yiisoft\Arrays\ArrayableInterface;

/**
 * VarDumper is intended to replace the PHP functions var_dump and print_r.
 * It can correctly identify the recursively referenced objects in a complex
 * object structure. It also has a recursive depth control to avoid indefinite
 * recursive display of some peculiar variables.
 *
 * VarDumper can be used as follows,
 *
 * ```php
 * VarDumper::dump($var);
 */

//https://github.com/ventoviro/windwalker-core/blob/e70121c1767c58e9fa22c4f50261084502cf870a/src/Core/Utilities/Debug/BacktraceHelper.php

// TODO : autre exemple : https://github.com/cakephp/cakephp/blob/master/src/Error/Debugger.php#L496
// https://github.com/nette/tester/blob/master/src/Framework/Dumper.php
//https://github.com/Lazer-rulezz/lazer-php-lib/blob/e8c29aa9b5de4bf975a82d19f97b7eacef0465b6/Formatter.php#L47
//https://github.com/lekoala/k/blob/ba4abd2079dc82c9635313504086e908c96cd1b5/src/k/ErrorHandler.php#L161

//https://github.com/Seldaek/monolog/blob/9776fa1d548716490a7d49ffbb608e5c2dda589b/src/Monolog/Formatter/NormalizerFormatter.php#L112

//https://github.com/nunomaduro/collision/blob/stable/src/ArgumentFormatter.php#L26

//https://github.com/symfony/var-exporter/blob/master/Internal/Exporter.php#L188

//https://github.com/cakephp/cakephp/blob/master/src/Error/Debugger.php#L508

class VarDumper
{
    private static $objects;

    private static $output;

    private static $depth;

    /**
     * Displays a variable.
     * This method achieves the similar functionality as var_dump and print_r
     * but is more robust when handling complex objects such as Yii controllers.
     *
     * @param mixed $var       variable to be dumped
     * @param int   $depth     maximum depth that the dumper should go into the variable. Defaults to 10.
     * @param bool  $highlight whether the result should be syntax-highlighted
     */
    public static function dump($var, int $depth = 10, bool $highlight = false): void
    {
        echo static::dumpAsString($var, $depth, $highlight);
    }

    /**
     * Dumps a variable in terms of a string.
     * This method achieves the similar functionality as var_dump and print_r
     * but is more robust when handling complex objects such as Yii controllers.
     *
     * @param mixed $var       variable to be dumped
     * @param int   $depth     maximum depth that the dumper should go into the variable. Defaults to 10.
     * @param bool  $highlight whether the result should be syntax-highlighted
     *
     * @return string the string representation of the variable
     */
    public static function dumpAsString($var, int $depth = 10, bool $highlight = false): string
    {
        self::$output = '';
        self::$objects = [];
        self::$depth = $depth;
        self::dumpInternal($var, 0);
        if ($highlight) {
            $result = highlight_string("<?php\n" . self::$output, true);
            self::$output = preg_replace('/&lt;\\?php<br \\/>/', '', $result, 1);
        }

        return self::$output;
    }

    /**
     * @param mixed $var   variable to be dumped
     * @param int   $level depth level
     */
    private static function dumpInternal($var, int $level): void
    {
        switch (gettype($var)) {
            case 'boolean':
                self::$output .= $var ? 'true' : 'false';

                break;
            case 'integer':
                self::$output .= (string) $var;

                break;
            case 'double':
                self::$output .= (string) $var;

                break;
            case 'string':
                self::$output .= "'" . addslashes($var) . "'";

                break;
            case 'resource':
                self::$output .= '{resource}';

                break;
            case 'NULL':
                self::$output .= 'null';

                break;
            case 'unknown type':
                self::$output .= '{unknown}';

                break;
            case 'array':
                if (self::$depth <= $level) {
                    self::$output .= '[...]';
                } elseif (empty($var)) {
                    self::$output .= '[]';
                } else {
                    $keys = array_keys($var);
                    $spaces = str_repeat(' ', $level * 4);
                    self::$output .= '[';
                    foreach ($keys as $key) {
                        self::$output .= "\n" . $spaces . '    ';
                        self::dumpInternal($key, 0);
                        self::$output .= ' => ';
                        self::dumpInternal($var[$key], $level + 1);
                    }
                    self::$output .= "\n" . $spaces . ']';
                }

                break;
            case 'object':
                if (($id = array_search($var, self::$objects, true)) !== false) {
                    self::$output .= get_class($var) . '#' . ($id + 1) . '(...)';
                } elseif (self::$depth <= $level) {
                    self::$output .= get_class($var) . '(...)';
                } else {
                    $id = array_push(self::$objects, $var);
                    $className = get_class($var);
                    $spaces = str_repeat(' ', $level * 4);
                    self::$output .= "$className#$id\n" . $spaces . '(';
                    if ('__PHP_Incomplete_Class' !== get_class($var) && method_exists($var, '__debugInfo')) {
                        $dumpValues = $var->__debugInfo();
                        if (! is_array($dumpValues)) {
                            throw new \Exception('__debugInfo() must return an array');
                        }
                    } else {
                        $dumpValues = (array) $var;
                    }
                    foreach ($dumpValues as $key => $value) {
                        $keyDisplay = strtr(trim($key), "\0", ':');
                        self::$output .= "\n" . $spaces . "    [$keyDisplay] => ";
                        self::dumpInternal($value, $level + 1);
                    }
                    self::$output .= "\n" . $spaces . ')';
                }

                break;
        }
    }

    /**
     * Exports a variable as a string representation.
     *
     * The string is a valid PHP expression that can be evaluated by PHP parser
     * and the evaluation result will give back the variable value.
     *
     * This method is similar to `var_export()`. The main difference is that
     * it generates more compact string representation using short array syntax.
     *
     * It also handles objects by using the PHP functions serialize() and unserialize().
     *
     * PHP 5.4 or above is required to parse the exported value.
     *
     * @param mixed $var the variable to be exported.
     *
     * @return string a string representation of the variable
     */
    public static function export($var): string
    {
        self::$output = '';
        self::exportInternal($var, 0);

        return self::$output;
    }

    /**
     * @param mixed $var   variable to be exported
     * @param int   $level depth level
     */
    private static function exportInternal($var, int $level): void
    {
        switch (gettype($var)) {
            case 'NULL':
                self::$output .= 'null';

                break;
            case 'array':
                if (empty($var)) {
                    self::$output .= '[]';
                } else {
                    $keys = array_keys($var);
                    $outputKeys = ($keys !== range(0, count($var) - 1));
                    $spaces = str_repeat(' ', $level * 4);
                    self::$output .= '[';
                    foreach ($keys as $key) {
                        self::$output .= "\n" . $spaces . '    ';
                        if ($outputKeys) {
                            self::exportInternal($key, 0);
                            self::$output .= ' => ';
                        }
                        self::exportInternal($var[$key], $level + 1);
                        self::$output .= ',';
                    }
                    self::$output .= "\n" . $spaces . ']';
                }

                break;
            case 'object':
                if ($var instanceof \Closure) {
                    self::$output .= self::exportClosure($var);
                } else {
                    try {
                        $output = 'unserialize(' . var_export(serialize($var), true) . ')';
                    } catch (\Exception $e) {
                        // serialize may fail, for example: if object contains a `\Closure` instance
                        // so we use a fallback
                        if ($var instanceof ArrayableInterface) {
                            self::exportInternal($var->toArray(), $level);

                            return;
                        }
                        if ($var instanceof \IteratorAggregate) {
                            $varAsArray = [];
                            foreach ($var as $key => $value) {
                                $varAsArray[$key] = $value;
                            }
                            self::exportInternal($varAsArray, $level);

                            return;
                        }
                        if ('__PHP_Incomplete_Class' !== get_class($var) && method_exists($var, '__toString')) {
                            $output = var_export($var->__toString(), true);
                        } else {
                            $outputBackup = self::$output;
                            $output = var_export(self::dumpAsString($var), true);
                            self::$output = $outputBackup;
                        }
                    }
                    self::$output .= $output;
                }

                break;
            default:
                self::$output .= var_export($var, true);
        }
    }

    /**
     * Exports a [[Closure]] instance.
     *
     * @param \Closure $closure closure instance.
     *
     * @return string
     */
    private static function exportClosure(\Closure $closure): string
    {
        $reflection = new \ReflectionFunction($closure);
        $fileName = $reflection->getFileName();
        $start = $reflection->getStartLine();
        $end = $reflection->getEndLine();
        if ($fileName === false || $start === false || $end === false) {
            return 'function() {/* Error: unable to determine Closure source */}';
        }
        $start--;
        $source = implode("\n", array_slice(file($fileName), $start, $end - $start));
        $tokens = token_get_all('<?php ' . $source);
        array_shift($tokens);
        $closureTokens = [];
        $pendingParenthesisCount = 0;
        foreach ($tokens as $token) {
            if (isset($token[0]) && $token[0] === T_FUNCTION) {
                $closureTokens[] = $token[1];

                continue;
            }
            if ($closureTokens !== []) {
                $closureTokens[] = $token[1] ?? $token;
                if ($token === '}') {
                    $pendingParenthesisCount--;
                    if ($pendingParenthesisCount === 0) {
                        break;
                    }
                } elseif ($token === '{') {
                    $pendingParenthesisCount++;
                }
            }
        }

        return implode('', $closureTokens);
    }
}
