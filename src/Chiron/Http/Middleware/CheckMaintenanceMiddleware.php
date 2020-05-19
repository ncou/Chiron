<?php

// TODO : regarder ici : https://gist.github.com/harini-ua/51d577023c7e8e7b6413a717b69c5dc5

// TODO : regarder ici comment c'est fait, on vire le cache si on n'a pas de valeur pour "Retry-After" ou un "Expires" header !!!! https://github.com/juliangut/janitor/blob/master/src/Handler/Render.php

//https://github.com/dappur/framework/blob/master/app/src/Middleware/Maintenance.php
/*
https://laracasts.com/discuss/channels/general-discussion/laravel-5-maintenance-mode
https://github.com/mnlg/maintenance-middleware/blob/master/src/Mnlg/Middleware/Maintenance.php
https://github.com/php-middleware/maintenance/blob/master/src/MaintenanceMiddleware.php
https://github.com/HavokInspiration/wrench/blob/master/src/Middleware/MaintenanceMiddleware.php
https://github.com/atst/stack-backstage/blob/master/src/Atst/StackBackstage.php
https://github.com/middlewares/shutdown/blob/master/src/Shutdown.php
https://github.com/luisinder/maintenance-middleware/blob/master/src/Maintenance.php

https://github.com/BootstrapCMS/CMS/blob/master/app/Http/Middleware/CheckForMaintenanceMode.php
https://github.com/ladybirdweb/momo-email-listener/blob/master/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/CheckForMaintenanceMode.php
https://github.com/jimrubenstein/laravel-framework/blob/master/src/Illuminate/Foundation/Http/Middleware/CheckForMaintenanceMode.php

// TODO : dans cet exemple on utilise un fichier json pour détecter si le site est down !!! Et il existe une commande via la Console pour activer/désactiver le mode maintenance
https://github.com/viserio/http-foundation/blob/4af138c4a188b9bebbaa07ee19ff92a68bf829ad/Middleware/CheckForMaintenanceModeMiddleware.php
https://github.com/laravel/framework/blob/0b12ef19623c40e22eff91a4b48cb13b3b415b25/src/Illuminate/Foundation/Http/Middleware/CheckForMaintenanceMode.php

*/

declare(strict_types=1);

namespace Chiron\Http\Middleware;

use Chiron\Http\Exception\Server\ServiceUnavailableHttpException;
use DateTimeInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

//https://github.com/php-middleware/maintenance/blob/master/src/MaintenanceMiddleware.php
//https://github.com/middlewares/shutdown/blob/master/src/Shutdown.php

class CheckMaintenanceMiddleware implements MiddlewareInterface
{
    /**
     * Dependency injection container.
     *
     * @var ContainerInterface
     */
    //private $container;

    //private const RETRY_AFTER = 'Retry-After';
    //private $handler;
    private $retryAfter;

    /* @var bool */
    private $isDown = false;

    /**
     * Set container.
     *
     * @param ContainerInterface $container
     */
    /*
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }*/

    /**
     * Estimated time when the downtime will be complete.
     * (integer for relative seconds or DateTimeInterface).
     *
     * @param DateTimeInterface|string|int $retryAfter
     */
    public function retryAfter($retryAfter): self
    {
        if ($retryAfter instanceof DateTimeInterface) {
            // TODO : à partir de la version 7.1.5 de PHP on peut utiliser la constante : \DateTime::RFC7231   qui est compliant avec la norme HTTP !!!!!!!!!!!
            //$retryAfter = $retryAfter->format('D, d M Y H:i:s \G\M\T'); //$retryAfter->format(\DateTime::RFC2822);  //'D, d M Y H:i:s e' // j'ai aussi vu un formatage en RFC1123 => gmdate(DATE_RFC1123, ...
            $retryAfter = $retryAfter->format(DATE_RFC7231);
        }

        $this->retryAfter = $retryAfter;

        return $this;
    }

    public function isDownForMaintenance(bool $isDown): self
    {
        $this->isDown = $isDown;

        return $this;
    }

    /**
     * Process a request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /*
        // TODO : ajouter le header "Retry-After" avec une gestion des options, genre durée de la maintenance, et durée avant de faire un refresh !!!!
        // https://github.com/php-middleware/maintenance/blob/master/src/MaintenanceMiddleware.php
                $headers = [];
                if ($this->retryAfter !== '') {
                    $headers['Retry-After'] = $this->retryAfter;
                    if ($this->refresh > 0) { // seconds
                        $headers['Refresh'] = (string) $this->refresh;
                    }
                }


        // TODO : version alternative pour ajouter le header !!!!
        //https://github.com/middlewares/shutdown/blob/master/src/Shutdown.php#L46
                if (is_int($this->retryAfter)) {
                    return $response->withHeader(self::RETRY_AFTER, (string) $this->retryAfter);
                }
                if ($this->retryAfter instanceof DateTimeInterface) {
                    return $response->withHeader(self::RETRY_AFTER, $this->retryAfter->format('D, d M Y H:i:s \G\M\T')); //$datetime->format(DateTime::RFC2822);
                }

        */

        //if ($this->app->isDownForMaintenance() && !in_array($this->request->getClientIp(), ['86.10.190.248', '86.4.7.24']))
//        $config = $this->container->config;
        //if ($config['settings.isDownForMaintenance']) {
        if ($this->isDown === true) {
            //return (new Response(503))->withHeader('Refresh', 10)->write('Be right back !');

            //$retryAfter = $config['settings.maintenanceRetryAfter'];

            /*
                        if (is_int($retryAfter)) {
                            $retryAfter = (string) $retryAfter;
                        }
            */

            throw new ServiceUnavailableHttpException($this->retryAfter);
        }

        /*
        //https://github.com/juliangut/janitor/blob/master/src/Handler/Render.php#L65
                if ($watcher instanceof ScheduledWatcherInterface) {
                    $response = $response
                        ->withHeader('Expires', $watcher->getEnd()->format('D, d M Y H:i:s e'))
                        ->withHeader('Retry-After', $watcher->getEnd()->format('D, d M Y H:i:s e'));
                } else {
                    $response = $response->withHeader('Cache-Control', 'max-age=0')
                        ->withHeader('Cache-Control', 'no-cache, must-revalidate')
                        ->withHeader('Pragma', 'no-cache');
                }
                return $response->withStatus(503)
                    ->withHeader('Content-Type', $contentType)
                    ->withBody($body);
        */

        //https://www.shellhacks.com/redirect-site-maintenance-page-apache-htaccess/
        // Header Set Cache-Control "max-age=0, no-store"

        return $handler->handle($request);
    }
}
