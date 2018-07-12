<?php

declare(strict_types=1);

namespace Chiron\Http\Middleware;

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
        //$contentTypeHeaders = $this->getHeader('Content-Type');
        //if (! empty($contentTypeHeaders)) {
        //    $contentType = end($contentTypeHeaders);
        //    ........

        // The presence of a message-body is signaled by the inclusion of a 'Content-Length' or 'Transfer-Encoding' header (cf rfc2616 / section 4.3)
        if ($request->hasHeader('Content-Length')) {
            $contentLength = (int) $request->getHeaderLine('Content-Length');

            if ($contentLength > $this->getPostMaxSize()) {
                throw new RequestEntityTooLargeHttpException();
            }
        }

        return $handler->handle($request);
    }

    /**
     * Determine the server 'post_max_size' as bytes.
     *
     * @return int size in byte
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
            default:
                return $byte;
        }
    }

    /*
      * Converts a shorthand byte value to an integer byte value.
      *
      * @since 2.3.0
      * @since 4.6.0 Moved from media.php to load.php.
      *
      * @link https://secure.php.net/manual/en/function.ini-get.php
      * @link https://secure.php.net/manual/en/faq.using.php#faq.using.shorthandbytes
      *
      * @param string $value A (PHP ini) byte value, either shorthand or ordinary.
      * @return int An integer byte value.
      */
/*
    function wp_convert_hr_to_bytes( $value ) {
        $value = strtolower( trim( $value ) );
        $bytes = (int) $value;

        if ( false !== strpos( $value, 'g' ) ) {
            $bytes *= GB_IN_BYTES;
        } elseif ( false !== strpos( $value, 'm' ) ) {
            $bytes *= MB_IN_BYTES;
        } elseif ( false !== strpos( $value, 'k' ) ) {
            $bytes *= KB_IN_BYTES;
        }

        // Deal with large (float) values which run into the maximum integer size.
        return min( $bytes, PHP_INT_MAX );
    }*/

    /*
     * Normalized a given value of memory limit into the number of bytes
     *
     * @param string|int $value
     * @throws Exception\InvalidArgumentException
     * @return int
     */
    /*
    protected function normalizeMemoryLimit($value)
    {
        if (is_numeric($value)) {
            return (int) $value;
        }
        if (!preg_match('/(\-?\d+)\s*(\w*)/', ini_get('memory_limit'), $matches)) {
            throw new Exception\InvalidArgumentException("Invalid  memory limit '{$value}'");
        }
        $value = (int) $matches[1];
        if ($value <= 0) {
            return 0;
        }
        switch (strtoupper($matches[2])) {
            case 'G':
                $value*= 1024;
                // no break
            case 'M':
                $value*= 1024;
                // no break
            case 'K':
                $value*= 1024;
                // no break
        }
        return $value;
    }*/

    /*
     * Return the maximum upload file size in bytes
     * @return string
     */
    /*
    protected function getMaximumUploadSize()
    {
        // Get the upload_max_filesize from the php.ini
        $upload_max_filesize = ini_get('upload_max_filesize');
        // Convert the value to bytes
        if (stripos($upload_max_filesize, 'K') !== false)
        {
            $upload_max_filesize = round($upload_max_filesize * 1024);
        }
        elseif (stripos($upload_max_filesize, 'M') !== false)
        {
            $upload_max_filesize = round($upload_max_filesize * 1024 * 1024);
        }
        elseif (stripos($upload_max_filesize, 'G') !== false)
        {
            $upload_max_filesize = round($upload_max_filesize * 1024 * 1024 * 1024);
        }
        return min($upload_max_filesize, $GLOBALS['TL_CONFIG']['maxFileSize']);
    }*/

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
