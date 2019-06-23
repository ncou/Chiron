<?php

if (! function_exists('csrf_field')) {
    /**
     * This generates a csrf field for html forms.
     *
     * @return string
     */
    function csrf_field()
    {
        return tag()->hiddenField([
            security()->getTokenKey(),
            'value' => security()->getToken(),
        ]);
    }
}

if (! function_exists('processing_time')) {
    /**
     * This calculates the processing time based on the starting time.
     *
     * @param int $starting_time The microtime it starts
     *
     * @return string
     */
    function processing_time($starting_time = 0)
    {
        return microtime(true) - $starting_time;
    }
}

if (! function_exists('iterate_require')) {
    /**
     * This iterates and require a php files, useful along folder_files().
     *
     * @param mixed $files
     *
     * @return mixed
     */
    function iterate_require(array $files)
    {
        $results = [];

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $results[basename($file, '.php')] = require $file;
            }
        }

        return $results;
    }
}

if (! function_exists('stubify')) {
    /**
     * This changes a stub format content.
     *
     * @return string
     */
    function stubify($content, $params)
    {
        foreach ($params as $key => $value) {
            $content = str_replace('{' . $key . '}', $value, $content);
        }

        return $content;
    }
}

if (! function_exists('path_to_namespace')) {
    /**
     * This converts a path into a namespace.
     *
     * @return string
     */
    function path_to_namespace($path)
    {
        $path = trim(str_replace('/', ' ', $path));
        $exploded_path = explode(' ', $path);

        $ret = [];

        foreach ($exploded_path as $word) {
            $ret[] = ucfirst($word);
        }

        return studly_case(implode('\\', $ret));
    }
}

if (! function_exists('url_trimmer')) {
    /**
     * This trims a url that has multiple slashes and trimming slash at the end.
     *
     * @return string
     */
    function url_trimmer($url)
    {
        return rtrim(preg_replace('/([^:])(\/{2,})/', '$1/', $url), '/');
    }
}

if (! function_exists('logging_extension')) {
    /**
     * This returns an extension name based on the requested logging time.
     *
     * @return string
     */
    function logging_extension()
    {
        $ext = '';

        switch ($logging_time = config()->app->logging_time) {
            case 'hourly':
                $ext = date('Y-m-d H-00-00');

            break;

            case 'daily':
                $ext = date('Y-m-d 00-00-00');

            break;

            case 'monthly':
                $ext = date('Y-m-0 00-00-00');

            break;

            case '':
            case null:
            case false:
                return $ext;

            break;

            default:
                throw new Exception('Logging time[' . $logging_time . '] not found');

            break;
        }

        return $ext;
    }
}

if (! function_exists('is_cli')) {
    /**
     * Check if the application is run in console mode.
     *
     * @return bool
     */
    function is_cli()
    {
        return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
    }
}

function dump()
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

function dump_d()
{
    call_user_func_array('dump', func_get_args());
    die();
}
