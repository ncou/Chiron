<?php

return [
    'commands' => [
        Chiron\Console\Command\Hello::class,
        Chiron\Console\Command\VersionCommand::class,
        Chiron\Console\Command\AboutCommand::class,
        Chiron\Console\Command\RuntimeDirCommand::class,
        Chiron\Console\Command\Package::class,
        Chiron\Console\Command\EncryptKeyCommand::class,
        Chiron\Console\Command\PublishCommand::class,
        Chiron\Console\Command\RouteListCommand::class,
        Chiron\Console\Command\ServeCommand::class,
    ],
];
