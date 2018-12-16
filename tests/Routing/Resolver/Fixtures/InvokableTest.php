<?php

declare(strict_types=1);

namespace Chiron\Tests\Routing\Resolver\Fixtures;

use Slim\Tests\Providers\PSR7ObjectProvider;

/**
 * Mock object for ControllerResolverTest.
 */
class InvokableTest
{
    public static $CalledCount = 0;

    public function __invoke()
    {
        return static::$CalledCount++;
    }
}
