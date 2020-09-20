<?php

declare(strict_types=1);

namespace Chiron\Logger;

use Chiron\Facade\Log;
use Psr\Log\LoggerAwareInterface;

final class LoggerAwareMutation
{
    public static function mutation(LoggerAwareInterface $class)
    {
        $class->setLogger(Log::getInstance());
    }
}
