<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception;

use Chiron\Http\Exception\VariantAlsoNegotiatesHttpException;

class VariantAlsoNegotiatesHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new VariantAlsoNegotiatesHttpException();
    }
}
