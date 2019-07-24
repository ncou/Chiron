<?php

declare(strict_types=1);

namespace Chiron\Config;

interface InjectableInterface
{
    public function getLinkedFile(): string;
}
