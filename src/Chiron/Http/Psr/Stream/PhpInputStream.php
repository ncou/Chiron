<?php

declare(strict_types=1);

//https://github.com/Wandu/Framework/blob/master/src/Wandu/Http/Psr/Stream/PhpInputStream.php
//https://github.com/zendframework/zend-diactoros/blob/master/src/PhpInputStream.php

//namespace Wandu\Http\Psr\Stream;

namespace Chiron\Http\Psr\Stream;

use Psr\Http\Message\StreamInterface;
use Wandu\Http\Psr\Stream;

class PhpInputStream extends StringStream implements StreamInterface
{
    public function __construct()
    {
        $stream = new Stream('php://input');
        parent::__construct($stream->__toString());
    }

    public function isWritable()
    {
        return false;
    }
}
