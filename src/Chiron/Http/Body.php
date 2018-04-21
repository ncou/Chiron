<?php

declare(strict_types=1);

namespace Chiron\Http;

// TODO : regarder aussi ici comment c'est fait : https://github.com/akrabat/rka-content-type-renderer/blob/master/src/SimplePsrStream.php
// https://github.com/akrabat/Slim-Http/blob/master/src/Stream.php

/*
 * @see       https://github.com/zendframework/zend-diactoros for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-diactoros/blob/master/LICENSE.md New BSD License
 */

//namespace Zend\Diactoros;

use Nyholm\Psr7\Stream;

/**
 * Implementation of PSR HTTP streams.
 */
class Body extends Stream
{
}
