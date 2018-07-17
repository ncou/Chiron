<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Client;

use Chiron\Http\Exception\Client\ConflictHttpException;
use Chiron\Tests\Http\Exception\HttpExceptionTest;

class ConflictHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new ConflictHttpException();
    }
}
