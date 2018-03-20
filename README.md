Micro-Framework
---------------

Chiron is a PHP micro framework that helps you quickly write simple yet powerful web applications and APIs.
All that is needed to get access to the Framework is to include the autoloader.

    <?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;
    
    require_once __DIR__.'/../vendor/autoload.php';
    
    $app = new Chiron\Application();
    $app->get('/hello/[:name]', function (Request $request, Response $response, string $name) {
        $response->getBody()->write('Hello ' . $name);
        return $response;
    });
    $app->run();

Next we define a route to /hello/[:name] that matches for GET http requests. When the route matches, the function is executed and the return value is sent back to the client as an http response.

Installation
------------

If you want to get started fast, use the Chiron Skeleton as a base by running this bash command :

    $ composer create-project chiron/chiron-skeleton [my-app-name]

Replace [my-app-name] with the desired directory name for your new application.

You can then run it with PHP's built-in webserver:

    $ cd [my-app-name]; php -S localhost:8080 -t public public/index.php

YOr using the Composer shortcut :

    $ composer start

If you want more flexibility, and install only the framework, use this Composer command instead:

    $ composer require chiron/chiron


