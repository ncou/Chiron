<?php

//https://github.com/ringcentral/psr7

//https://github.com/Wandu/Framework/blob/master/src/Wandu/Http/functions.php
//https://github.com/Wandu/Framework/blob/df04fdb44928201217e11fe211ddf9cc7c21ef6e/src/Wandu/Http/composer.json#L15

//https://github.com/guzzle/psr7/blob/master/src/functions.php
//https://github.com/guzzle/psr7/blob/master/src/functions_include.php
//https://github.com/guzzle/psr7/blob/7fa8852adec06dabfbde1b028c4c9d9087558256/composer.json#L33

// TODO : ajouter la méthode str() de guzzle pour serializer une request ou response + ajouter la méthode create stream_for qui va créer un stream.

// TODO : ajouter une fonction pour formater une date en GMT
/*
 //A RFC2616 compliant subset of RFC1123.
 //Example: Sun, 06 Nov 1994 08:49:37 GMT
const DATE_RFC7231 = 'D, d M Y H:i:s \G\M\T';*/

/*
// new constant is defined since PHP7.1.5 !!!!!!!! => \DateTime::RFC7231
if (!defined(‘DATE_RFC7231’)) {
define(‘DATE_RFC7231’, ‘D, d M Y H:i:s \G\M\T’);
}*/

/*

if ($retryAfter instanceof DateTimeInterface) {
    $retryAfter = $retryAfter->format('D, d M Y H:i:s \G\M\T'); //$retryAfter->format(DateTime::RFC2822);  //'D, d M Y H:i:s e' // j'ai aussi vu un formatage en RFC1123 => gmdate(DATE_RFC1123, ...
}

// gm_date('D, d M Y H:i:s').' GMT'

->withHeader('Expires', $watcher->getEnd()->format('D, d M Y H:i:s e'))
->withHeader('Retry-After', $watcher->getEnd()->format('D, d M Y H:i:s e'));

*/
/*
     * Create an HTTP date (RFC 1123 / RFC 822) formatted UTC date-time string
     *
     * @param string|integer|\DateTime $value Date time value
     *
     * @return string
     */
    /*
    private function formatDateTimeHttp($value)
    {
        return $this->dateFormatter($value, 'D, d M Y H:i:s \G\M\T');
    }*/

    /*
     * Perform the actual DateTime formatting
     *
     * @param int|string|\DateTime $dateTime Date time value
     * @param string               $format   Format of the result
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    /*
    protected function dateFormatter($dateTime, $format)
    {
        if (is_numeric($dateTime)) {
            return gmdate($format, (int) $dateTime);
        }
        if (is_string($dateTime)) {
            $dateTime = new \DateTime($dateTime);
        }
        if ($dateTime instanceof \DateTimeInterface) {
            static $utc;
            if (!$utc) {
                $utc = new \DateTimeZone('UTC');
            }
            return $dateTime->setTimezone($utc)->format($format);
        }
        throw new \InvalidArgumentException('Date/Time values must be either '
            . 'be a string, integer, or DateTime object');
    }*/

/*
    private function initDate()
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $now = \DateTimeImmutable::createFromMutable($now);
        return $now->format('D, d M Y H:i:s').' GMT';
    }
*/

/**
 * A RFC7231 Compliant date.
 *
 * http://tools.ietf.org/html/rfc7231#section-7.1.1.1
 *
 * Example: Sun, 06 Nov 1994 08:49:37 GMT
 *
 * This constant was introduced in PHP 7.0.19 and PHP 7.1.5 but needs to be defined for earlier PHP versions.
 */
if (! defined('DATE_RFC7231')) {
    define('DATE_RFC7231', 'D, d M Y H:i:s \G\M\T');
}

namespace Wandu\Http
{
    use Wandu\Http\Factory\ResponseFactory;

    /**
     * @return \Wandu\Http\Factory\ResponseFactory
     */
    function response()
    {
        if (! isset(ResponseFactory::$instance)) {
            ResponseFactory::$instance = new ResponseFactory();
        }

        return ResponseFactory::$instance;
    }
    /**
     * @reference https://gist.github.com/Mulkave/65daabb82752f9b9a0dd
     *
     * @param string $url
     *
     * @return array|bool
     */
    function parseUrl($url)
    {
        $parts = parse_url(preg_replace_callback('/[^:\/@?&=#]+/u', function ($matches) {
            return urlencode($matches[0]);
        }, $url));
        if ($parts === false) {
            return false;
        }
        foreach ($parts as $name => $value) {
            $parts[$name] = ($name === 'port') ? $value : urldecode($value);
        }

        return $parts;
    }
}

namespace Wandu\Http\Response
{
    use Closure;
    use Generator;
    use Psr\Http\Message\ServerRequestInterface;
    use Traversable;
    use Wandu\Http\Exception\BadRequestException;
    use function Wandu\Http\response;

    /**
     * @param string $content
     * @param int    $status
     * @param array  $headers
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    function create($content = null, $status = 200, array $headers = [])
    {
        return response()->create($content, $status, $headers);
    }
    /**
     * @param \Closure $area
     * @param int      $status
     * @param array    $headers
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    function capture(Closure $area, $status = 200, array $headers = [])
    {
        return response()->capture($area, $status, $headers);
    }
    /**
     * @param string|array $data
     * @param int          $status
     * @param array        $headers
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    function json($data = [], $status = 200, array $headers = [])
    {
        return response()->json($data, $status, $headers);
    }
    /**
     * @param string $file
     * @param string $name
     * @param array  $headers
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    function download($file, $name = null, array $headers = [])
    {
        return response()->download($file, $name, $headers);
    }
    /**
     * @param string $path
     * @param array  $queries
     * @param int    $status
     * @param array  $headers
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    function redirect($path, $queries = [], $status = 302, $headers = [])
    {
        $parsedQueries = [];
        foreach ($queries as $key => $value) {
            $parsedQueries[] = "{$key}=" . urlencode($value);
        }
        if (count($parsedQueries)) {
            $path .= '?' . implode('&', $parsedQueries);
        }

        return response()->redirect($path, $status, $headers);
    }
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @throws \Wandu\Http\Exception\BadRequestException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    function back(ServerRequestInterface $request)
    {
        if ($request->hasHeader('referer')) {
            return redirect($request->getHeader('referer'));
        }

        throw new BadRequestException();
    }
    /**
     * @deprecated use iterator
     *
     * @param \Generator $generator
     * @param int        $status
     * @param array      $headers
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    function generator(Generator $generator, $status = 200, array $headers = [])
    {
        return response()->iterator($generator, $status, $headers);
    }
    /**
     * @param \Traversable $iterator
     * @param int          $status
     * @param array        $headers
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    function iterator(Traversable $iterator, $status = 200, array $headers = [])
    {
        return response()->iterator($iterator, $status, $headers);
    }
}
