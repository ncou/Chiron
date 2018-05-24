<?php

// https://github.com/Wandu/Framework/blob/master/src/Wandu/Http/Psr/Stream.php

namespace Wandu\Http\Psr;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Wandu\Http\Psr\Stream\ResourceStream;

class Stream extends ResourceStream implements StreamInterface
{
    /**
     * @param string $stream
     * @param string $mode
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $stream = 'php://memory', string $mode = 'r')
    {
        $error = null;

        set_error_handler(function ($e) use (&$error) {
            $error = $e;
        }, E_WARNING);
        $resource = fopen($stream, $mode);
        restore_error_handler();

        if ($error) {
            throw new InvalidArgumentException('Invalid stream reference provided');
        }

        parent::__construct($resource);

        /*
        $resource = @fopen($stream, $mode);
        if (!$resource) {
            throw new InvalidArgumentException(
                "Invalid stream \"{$stream}\". It must be a valid path with valid permissions."
            );
        }
        parent::__construct($resource);*/
    }
}
