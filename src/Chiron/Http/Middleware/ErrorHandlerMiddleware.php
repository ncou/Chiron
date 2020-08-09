<?php

declare(strict_types=1);

// originalRequest : https://github.com/zendframework/zend-expressive/blob/c6db5b1a7524414eee0637bb50b8eed32fd67794/src/Middleware/WhoopsErrorResponseGenerator.php

// Gérer le cas ou il y a une erreur en interne dans le handler :
//https://github.com/cakephp/cakephp/blob/master/src/Error/Middleware/ErrorHandlerMiddleware.php#L138
//https://github.com/cakephp/cakephp/blob/2341c3cd7c32e315c2d54b625313ef55a86ca9cc/src/Error/ErrorHandler.php#L141
//https://github.com/yiisoft/yii2/blob/master/framework/base/ErrorHandler.php#L121

// régle le niveau d'affichage des erreurs :
//******************************************
//https://github.com/laravel/framework/blob/master/src/Illuminate/Foundation/Bootstrap/HandleExceptions.php#L28

//https://github.com/laravel/framework/blob/master/src/Illuminate/Foundation/Bootstrap/HandleExceptions.php

//https://github.com/Lansoweb/api-problem/blob/master/src/ErrorResponseGenerator.php   +   https://github.com/Lansoweb/api-problem/blob/master/src/Model/ApiProblem.php

//************************************
// TODO : comment faire des tests du middleware : https://github.com/cakephp/cakephp/blob/master/tests/TestCase/Error/Middleware/ErrorHandlerMiddlewareTest.php
//https://github.com/l0gicgate/Slim/blob/4.x-ErrorMiddleware/tests/Middleware/ErrorMiddlewareTest.php
//************************************

// TODO : regarder ici : https://github.com/cakephp/cakephp/blob/master/src/Error/Middleware/ErrorHandlerMiddleware.php

// TODO : attacher des listeners pour permettre de logger les erreurs par exemple !!!!! https://github.com/zendframework/zend-stratigility/blob/master/src/Middleware/ErrorHandler.php#L116    +  https://docs.zendframework.com/zend-stratigility/v3/error-handlers/

//https://github.com/middlewares/error-handler/blob/master/src/ErrorHandler.php

// TODO : regarder ici pour la gestion des logs : https://github.com/juliangut/slim-exception/blob/master/src/ExceptionManager.php#L285

// TODO : regarder ici : https://github.com/zendframework/zend-stratigility/blob/master/src/Middleware/ErrorHandler.php

// TODO : regarder ici : https://github.com/cakephp/cakephp/blob/master/src/Error/Middleware/ErrorHandlerMiddleware.php

// TODO : regarder ici comment c'est fait : https://github.com/zendframework/zend-problem-details/blob/master/src/ProblemDetailsMiddleware.php

// TODO : faire un clear output avant d'envoyer la réponse ???? https://github.com/cakephp/cakephp/blob/2341c3cd7c32e315c2d54b625313ef55a86ca9cc/src/Error/ErrorHandler.php#L138

//-----------------
//https://github.com/samsonasik/ErrorHeroModule/blob/master/src/HeroTrait.php#L22
//https://github.com/samsonasik/ErrorHeroModule/blob/master/src/Middleware/Expressive.php#L59

//********* EXCEPTION MANAGER ****************

// WHOOPS + Template 404...etc
//https://github.com/laravel/framework/blob/master/src/Illuminate/Foundation/Exceptions/Handler.php
//https://github.com/laravel/framework/blob/master/src/Illuminate/Foundation/Exceptions/WhoopsHandler.php
//https://github.com/laravel/framework/blob/master/src/Illuminate/Foundation/Bootstrap/HandleExceptions.php
//https://github.com/laravel/framework/tree/master/src/Illuminate/Foundation/Exceptions/views

// Ajouter dans le fichier .env des variable pour gérer les exceptions :
//APP_ENV=dev|prod
//APP_DEBUG=true|false
//APP_KEY=SomeRandomString    <= à utiliser pour le cookie encrypt par exemple
//APP_LOG_LEVEL="debug"

// TODO : regarder ici pour gérer les template en cas d'erreurs (fatfree framework)
//https://github.com/vijinho/f3-boilerplate/blob/3d3f8169bc3a73ccd09c2b45e61dbe5b88b4d845/app/lib/App/App.php

//https://github.com/cakephp/cakephp/blob/master/src/Error/BaseErrorHandler.php#L390
//https://github.com/Seldaek/monolog/blob/master/src/Monolog/ErrorHandler.php#L125

//https://github.com/symfony/symfony/blob/e60a876201b5b306d0c81a24d9a3db997192079c/src/Symfony/Component/ErrorHandler/Debug.php

namespace Chiron\Http\Middleware;

use Chiron\ErrorHandler\ErrorManager;
use Chiron\Http\Psr\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

// TODO : déplacer le middleware de gestion des Errors ErrorHandlerMiddleware dans le répertoire "ErrorHandler"
// TODO : passer la classe en final ? + virer les propriétés protected ????

class ErrorHandlerMiddleware implements MiddlewareInterface
{
    /**
     * @var ErrorManager
     */
    private $errorManager;

    public function __construct(ErrorManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Process a server request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $response = $handler->handle($request);
        } catch (Throwable $exception) {
            $response = $this->manager->renderException($exception, $request);
        }

        return $response;
    }
}
