<?php

declare(strict_types=1);

namespace Chiron\Logger;

use Chiron\Container\Container;
use Chiron\Facade\Log;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

final class LoggerAwareMutation
{
    public static function mutation(LoggerAwareInterface $class)
    {
        $class->setLogger(Log::getInstance());
    }
}
