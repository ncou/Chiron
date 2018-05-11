<?php
/**
 * @see       https://github.com/zendframework/zend-stratigility for the canonical source repository
 *
 * @copyright Copyright (c) 2016-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-stratigility/blob/master/LICENSE.md New BSD License
 */
declare(strict_types=1);

namespace Chiron\Tests\Http\Exception;

use Chiron\Http\Exception\RequestEntityTooLargeHttpException;

class RequestEntityTooLargeHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new RequestEntityTooLargeHttpException();
    }
}
