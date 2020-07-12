<?php

return [

    // TODO : normalement on devrait avoir un tableau vide et les providers ci dessous seraient chargés soit par le PackageManifest qui scan les packages, soit via le app.php pour le dernier provider (database)
    'providers' => [
        Chiron\Provider\ServerRequestCreatorServiceProvider::class,
        Chiron\Provider\HttpFactoriesServiceProvider::class,
        Chiron\Provider\LoggerServiceProvider::class,
        Chiron\Provider\ErrorHandlerServiceProvider::class,
        Chiron\Provider\RoadRunnerServiceProvider::class,

        // TODO : cela doit être déplace dans le fichier composer.json des packages fastrouterouter et phprenderer
        //Chiron\Router\FastRoute\Provider\FastRouteRouterServiceProvider::class,
        //Chiron\Views\Provider\PhpRendererServiceProvider::class,
    ],

    'bootloaders' => [


        //Chiron\Bootloader\NoBootingBootloader::class,


        Chiron\Bootloader\PublishableCollectionBootloader::class,
        Chiron\Bootloader\PackageManifestBootloader::class,

        // TODO : attention si il y a des bootloaders chargés via le packagemanifest qui ajoutent une commande dans la console, si cette commande utilise le même nom que les commandes par défaut  définies dans la classe CommandBootloader, elles vont être écrasées !!!! faut il faire un test dans cette classe si la command est déjà définie dans la console on ne l'ajoute pas ????? ou alors écrase la commande d'office ????
        Chiron\Bootloader\CommandBootloader::class,



        // TODO : déplacer ces bootloader dans les packages templates/router/http et ajouter dans le composer.json une balise extra avec les informations pour charger ces classes.
        //Chiron\Bootloader\ViewBootloader::class,
        Chiron\Bootloader\HttpBootloader::class,
        //Chiron\Bootloader\RouteCollectorBootloader::class,

        Chiron\Bootloader\ConsoleBootloader::class,
        Chiron\Bootloader\ApplicationBootloader::class,
    ],
];
