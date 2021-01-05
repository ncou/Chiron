<?php

/**
 * This calls the sub-files.
 */
//require __DIR__ . '/container.php';
//require __DIR__ . '/random.php';
//require __DIR__ . '/chiron.php';
require __DIR__ . '/paths.php';
//require __DIR__ . '/misc.php';
require __DIR__ . '/misc2.php';
require __DIR__ . '/facade.php';
require __DIR__ . '/url.php';


use Chiron\Support\Dump;

function dd2()
{
    $args = func_get_args();

    echo "\n<pre style=\"border:1px solid #ccc;padding:10px;margin:10px;font:14px courier;background:whitesmoke;display:block;border-radius:4px;\">\n";

    $trace = debug_backtrace(false);
    $offset = (@$trace[2]['function'] === 'dump_d') ? 2 : 0;

    echo '<span style="color:red">' . @$trace[1 + $offset]['class'] . '</span>:' .
       '<span style="color:blue;">' . @$trace[1 + $offset]['function'] . '</span>:' .
       @$trace[0 + $offset]['line'] . ' ' .
       '<span style="color:green;">' . @$trace[0 + $offset]['file'] . "</span>\n";

    if (! empty($args)) {
        echo "\n";
        call_user_func_array('var_dump', $args);
    }

    echo "</pre>\n";
}



if (!function_exists('dd')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param  mixed
     * @return void
     */
    function dd()
    {
        call_user_func_array('dump', func_get_args());

        die(1);
    }
}

if (!function_exists('dump')) {
    /**
     * Dump the passed variables without end the script.
     *
     * @param  mixed
     * @return void
     */
    function dump()
    {
        array_map(function ($x) {
            $string = (new Dump([], true))->variable($x);

            echo (PHP_SAPI == 'cli' ? strip_tags($string) . PHP_EOL : $string);

        }, func_get_args());
    }
}

if (!function_exists('dump2')) {
    /**
     * Dump the passed variables without end the script.
     *
     * @param  mixed
     * @return void
     */
    function dump2()
    {
        array_map(function ($x) {
            $string = var_dump($x);

            echo (PHP_SAPI == 'cli' ? strip_tags($string) . PHP_EOL : $string);

        }, func_get_args());
    }
}

if (!function_exists('dump3')) {
    /**
     * Dump the passed variables without end the script.
     *
     * @param  mixed
     * @return void
     */
    function dump3()
    {
        array_map(function ($x) {
            $string = (new Chiron\Support\VarDumper())->dump($x);

            echo (PHP_SAPI == 'cli' ? strip_tags($string) . PHP_EOL : $string);

        }, func_get_args());
    }
}
