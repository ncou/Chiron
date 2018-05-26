<?php

declare(strict_types=1);

namespace Chiron\Http\Psr\Stream;

use Psr\Http\Message\StreamInterface;

/**
 * Does not store any data written to it.
 * iinspired by the Guzzle NullStream Class.
 */
class NullStream implements StreamInterface
{
    private const EMPTY = '';

    public function close()
    {
    }

    public function detach()
    {
    }

    public function rewind()
    {
    }

    public function getMetadata($key = null)
    {
        return $key ? null : [];
    }

    public function eof()
    {
        return true;
    }

    public function isSeekable()
    {
        return true;
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        return false;
    }

    public function isWritable()
    {
        return true;
    }

    public function isReadable()
    {
        return true;
    }

    public function write($string)
    {
        return 0;
    }

    public function getSize()
    {
        return 0;
    }

    public function tell()
    {
        return 0;
    }

    public function read($length)
    {
        return self::EMPTY;
    }

    public function getContents()
    {
        return self::EMPTY;
    }

    public function __toString()
    {
        return self::EMPTY;
    }
}
