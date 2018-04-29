<?php

/**
 * Chiron (http://www.chironframework.com).
 *
 * @see      https://github.com/ncou/Chiron
 *
 * @license   https://github.com/ncou/Chiron/blob/master/licenses/LICENSE.md (MIT License)
 */

//https://github.com/userfrosting/UserFrosting/blob/master/app/system/ServicesProvider.php
//https://github.com/slimphp/Slim/blob/3.x/Slim/DefaultServicesProvider.php

namespace Chiron;

use Chiron\Routing\Router;
use Psr\Container\ContainerInterface;
use Psr\Log\NullLogger;

/**
 * Chiron system services provider.
 *
 * Registers system services for Chiron, such as config manager, middleware router and dispatcher...
 */
class DefaultServicesProvider
{
    /**
     * Register UserFrosting's system services.
     *
     * @param ContainerInterface $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register(ContainerInterface $container)
    {
        // TODO : initialiser un logger ici ???? et éventuellement créer une propriété pour changer le formater dans la restitution de la log. cf nanologger et la liste des todo pour mettre un formater custom à passer en paramétre du constructeur !!!!

        $container['router'] = function ($c) {
            return new Router();
        };

        $container['logger'] = function ($c) {
            return new NullLogger();
        };

        /*
           $container['callableResolver'] = function ($container) {
               return new CallableResolver($container);
           };
        */

        //$this->factory = new MiddlewareFactory($this->container);

        //$this->loadConfig($config_path_or_file_or_array, $config_cache_file);

        // TODO : déplacer ces initialisations dans le constructeur d'une classe CONTAINER externalisée

        // TODO : ajouter l'initialisation d'un logger ?????

        // TODO : vérifier l'utilité de mettre cela dans un container, normalement on va toujours passer par le router, donc le mettre dans un container n'est pas vraiment nécessaire, surtout que dans les controller on ne va pas réutiliser le router, car la méthode redirect ou getPathFor se trouve directement dans $app et pas dans la classe Router.
        // register the router in the pimple container

        /*
            $this['session'] = function ($c) {
                // TODO : déplacer la classe session dans le répertoire "components"
                return new Session();
            };
        */

        /*
            $this['router'] = function ($c) {
                return new Router($c->get('basePath'), $this->container);
            };
        */

        // Create request class closure.
        /*
            $this['request'] = function ($c) {
                return Request::fromGlobals();
            };
        */

        // TODO : à virer car maintenant la réponse est créée directement dans le controler. il faudrait plutot utiliser une ResponseFactory appellé directement dans le controller !!!
        // TODO : vérifier l'utilité de créer cette response ici !!!!! normalement chaque controller ou errorhandler va créer une nouvelle response...
        $container['response'] = function ($c) {
            //$headers = new Headers(['Content-Type' => 'text/html; charset=UTF-8']);
            //$response = new Response(200, $headers);
            //return $response->withProtocolVersion($container->get('settings')['httpVersion']);

            // TODO : à améliorer il faut passer le header text/heml et charset UTF8 par défaut + le code de réposne à 200 + si c'est du http ou https !!!!!!!!!
            $response = new Response();
            // TODO : ajouter 2 lignes "Content-Type" avec text/html puis avec charset=XXXX, ca fera la même chose qu'une ligne séparée avec une virgule
            // TODO : récuperer le charset directement dans la partie ->getContainer()->get('config') !!!!
            $response = $response->withAddedHeader('Content-Type', 'text/html; charset=' . $c->get('charset'));
            //$response->setProtocolVersion($c->get('httpVersion'));
            return $response;
        };
    }
}
