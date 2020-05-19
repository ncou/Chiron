<?php

// TODO : la liste des disptachers devrait plut être uniquement au niveau du fichier app.php et pas core.php
return [
    'dispatchers' => [
        Chiron\Dispatcher\ConsoleDispatcher::class,
        Chiron\Dispatcher\SapiDispatcher::class,
        Chiron\Dispatcher\RrDispatcher::class,
        //Chiron\Dispatcher\ReactDispatcher::class,
    ],

// TODO : normalement on devrait avoir un tableau vide et les providers ci dessous seraient chargés soit par le PackageManifest qui scan les packages, soit via le app.php pour le dernier provider (database)
    'providers' => [
        Chiron\Provider\ServerRequestCreatorServiceProvider::class,
        Chiron\Provider\HttpFactoriesServiceProvider::class,
        Chiron\Provider\LoggerServiceProvider::class,
        Chiron\Provider\MiddlewaresServiceProvider::class,
        Chiron\Provider\ErrorHandlerServiceProvider::class,
        Chiron\Provider\RoadRunnerServiceProvider::class,



        // TODO : cela doit être déplace dans le fichier composer.json des packages fastrouterouter et phprenderer
        //Chiron\Router\FastRoute\Provider\FastRouteRouterServiceProvider::class,
        Chiron\Views\Provider\PhpRendererServiceProvider::class,
    ],

    'bootloaders' => [
        Chiron\Bootloader\CommandBootloader::class,
        Chiron\Bootloader\PublishableCollectionBootloader::class,
        Chiron\Bootloader\PackageManifestBootloader::class,





        // TODO : déplacer ces bootloader dans les packages templates/router/http et ajouter dans le composer.json une balise extra avec les informations pour charger ces classes.
        Chiron\Bootloader\ViewBootloader::class,
        Chiron\Bootloader\HttpBootloader::class,
        //Chiron\Bootloader\RouteCollectorBootloader::class,
    ],
];
