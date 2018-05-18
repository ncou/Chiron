<?php

declare(strict_types=1);

namespace Chiron\Middleware;

//https://github.com/withelmo/CakePHP-PostMaxSizeException/blob/master/Lib/PostMaxSizeChecker.php

use Chiron\Http\Exception\RequestEntityTooLargeHttpException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class BodyLimitMiddleware implements MiddlewareInterface
{
    /**
     * Process a request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /*
            if (! ($request->isMethod('POST') || $resquest->isMethod('PUT'))) {
                return $handler->handle($request);
            }
        */

        $maxSize = $this->getPostMaxSize();
        $contentLength = (int) $request->getHeaderLine('Content-Length');

        //if ($maxSize > 0 && $contentLength > $maxSize) {
        if ($contentLength > $maxSize) {
            throw new RequestEntityTooLargeHttpException();
        }

        return $handler->handle($request);
    }

    /**
     * Determine the server 'post_max_size' as bytes.
     *
     * @return int
     */
    protected function getPostMaxSize(): int
    {
        $postMaxSize = strtoupper(trim(ini_get('post_max_size')));
        // only string
        $unit = preg_replace('/[^a-zA-Z]/', '', $postMaxSize);
        // only number (allow decimal point)
        $byte = (int) preg_replace('/\D\.\D/', '', $postMaxSize);

        switch ($unit) {
            case 'K':
                return $byte * 1024;
            case 'M':
                return $byte * 1024 * 1024;
            case 'G':
                return $byte * 1024 * 1024 * 1024;
            case 'T':
                return $byte * 1024 * 1024 * 1024 * 1024;
            case 'P':
                return $byte * 1024 * 1024 * 1024 * 1024 * 1024;
            default:
                return $byte;
        }
    }

    /*
     * Gets maximum post request size of attachment from php ini settings.
     * post_max_size specifies maximum size of a post request,
     * we are uploading attachment using post method
     *
     * @return int returns the post request size as bytes.
     */
    /*
    function getPostMaxSize2(): int
    {
        $post_max_value = strtoupper(ini_get('post_max_size'));

        // calculate post_max_value value to bytes
        if (strpos($post_max_value, "K")!== false){
            $post_max_value = ((int) $post_max_value) * 1024;
        } else if (strpos($post_max_value, "M")!== false){
            $post_max_value = ((int) $post_max_value) * 1024 * 1024;
        } else if (strpos($post_max_value, "G")!== false){
            $post_max_value = ((int) $post_max_value) * 1024 * 1024 * 1024;
        }

        return (int) $post_max_value;
    }*/

/*
    public static function getPostMaxSize3()
    {
        $val  = ini_get('post_max_size');
        $last = strtolower($val[strlen($val) - 1]);
        switch ($last)
        {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $val;
    }*/

    /*
     * Determine the server 'post_max_size' as bytes.
     *
     * @return int
     */
    /*
    protected function getPostMaxSize4(): int
    {
        if (is_numeric($postMaxSize = ini_get('post_max_size'))) {
            return (int) $postMaxSize;
        }

        $metric = strtoupper(substr($postMaxSize, -1));

        switch ($metric) {
            case 'K':
                return (int) $postMaxSize * 1024;
            case 'M':
                return (int) $postMaxSize * 1024 * 1024;
            case 'G':
                return (int) $postMaxSize * 1024 * 1024 * 1024;
            default:
                return (int) $postMaxSize;
        }
    }*/

/*
    public function getPostMaxSizeBytes()
    {
        $post_max_size = ini_get('post_max_size');
        $bytes         = trim($post_max_size);
        $last          = strtolower($post_max_size[strlen($post_max_size) - 1]);

        switch ($last)
        {
            case 'g': $bytes *= 1024;
            case 'm': $bytes *= 1024;
            case 'k': $bytes *= 1024;
        }

        if ($bytes == '')
            $bytes = null;
        return $bytes;
    }*/

    /*
     * byte変換
     * ※10MBの文字列を10485760のようなbyte数に変換
     *
     * @param string $size ini_get('upload_max_filesize')の戻り値である「10M」のような文字列
     * @return (number|boolean)
     */
    /*
    private function __toByte($size = null) {
        if (empty($size)) {
            return false;
        }
        preg_match_all('/^(\d*)(K|M|G|T|P)$/i', $size, $matches);
        $base = null;
        if (!empty($matches[1][0])) {
            $base = $matches[1][0];
        }
        $unit = null;
        if (!empty($matches[2][0])) {
            $unit = strtoupper($matches[2][0]);
        }
        if (empty($base) || empty($unit)) {
            return false;
        }
        switch ($unit) {
            case 'K':
                $result = $base * 1024;
                break;
            case 'M':
                $result = $base * 1024 * 1024;
                break;
            case 'G':
                $result = $base * 1024 * 1024 * 1024;
                break;
            case 'T':
                $result = $base * 1024 * 1024 * 1024 * 1024;
                break;
            case 'P':
                $result = $base * 1024 * 1024 * 1024 * 1024 * 1024;
                break;
            default:
                $result = false;
                break;
        }
        return $result;
    }*/
}
