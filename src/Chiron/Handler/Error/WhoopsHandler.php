<?php

declare(strict_types=1);

namespace Chiron\Handler\Error;

use Chiron\Http\Exception\HttpException;
use Chiron\Http\Psr\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use UnexpectedValueException;
use Whoops\Handler\HandlerInterface;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\XmlResponseHandler;
use Whoops\Run as Whoops;
use Whoops\Util\Misc;

// TODO : regarder ici pour gérer les formater pour les messages : https://github.com/userfrosting/UserFrosting/blob/master/app/sprinkles/core/src/Error/ExceptionHandlerManager.php

// TODO : utiliser des renderer : https://github.com/userfrosting/UserFrosting/tree/master/app/sprinkles/core/src/Error/Renderer

//https://github.com/dopesong/Slim-Whoops/blob/2.x/src/Whoops.php

// TODO : créer une interface
class WhoopsHandler implements ExceptionHandlerInterface
{
    use DeterminesContentTypeTrait;

    public const DEFAULT_STATUS_CODE = 500;
    /**
     * The request attribute name used to retrieve the exception stored previously (in the middleware).
     *
     * @var string
     */
//    protected $attributeName = 'Chiron:exception';

    /**
     * @var WhoopsRun
     */
    protected $whoops;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->whoops = new Whoops();
        $this->whoops->writeToOutput(false);
        $this->whoops->allowQuit(false);
    }

    public function handleException(Throwable $exception, ServerRequestInterface $request): ResponseInterface
    {
        $contentType = $this->determineContentType($request);

        $this->pushHandlerByContentType($contentType);
        $output = $this->whoops->handleException($exception);

        return $this->respond($exception, $output);
    }

    /**
     * @param callable|HandlerInterface $handler
     *
     * @throws \InvalidArgumentException If argument is not callable or instance of HandlerInterface
     */
    public function pushHandler($handler)
    {
        $this->whoops->pushHandler($handler);
    }

    /**
     * @param Throwable $e
     * @param string    $body
     *
     * @return ResponseInterface
     */
    protected function respond(Throwable $e, string $body)
    {
        // TODO : lui passer plutot une factory en paramétre du constructeur de la classe comme ca on évite de rendre cette classe adhérente à la classe "Chiron\Http\Response"
        $response = new Response(self::DEFAULT_STATUS_CODE);

        if ($e instanceof HttpException) {
            // add the headers stored in the exception
            $headers = $e->getHeaders();
            foreach ($headers as $header => $value) {
                $response = $response->withAddedHeader($header, $value);
            }
            $response = $response->withStatus($e->getStatusCode());
        }

        return $response->write($body);
    }

    /**
     * @param string $contentType
     */
    protected function pushHandlerByContentType(string $contentType)
    {
        /*
        if (Misc::isAjaxRequest()) {
            $this->prependHandler(new JsonResponseHandler());
            return;
        }*/

        switch ($contentType) {
            case 'application/json':
                $contentTypeBasedHandler = new JsonResponseHandler();
                $contentTypeBasedHandler->addTraceToOutput(true);

                break;
            case 'text/xml':
            case 'application/xml':
                $contentTypeBasedHandler = new XmlResponseHandler();
                $contentTypeBasedHandler->addTraceToOutput(true);

                break;
            case 'text/plain':
                $contentTypeBasedHandler = new PlainTextHandler();
                $contentTypeBasedHandler->addTraceToOutput(true);

                break;
            // TODO : attention dans le cas du default (cas que le content-type n'est pas dans la liste du switch) il faudrait écraser le content-type utilisé pour le mettre à text/html, sinon on aura un body non cohérent avec le content-type
            default:
            case 'text/html':
                $contentTypeBasedHandler = new PrettyPageHandler();
                //$contentTypeBasedHandler->handleUnconditionally(true);
                // blacklist some .env values (just in case because in production, you should not use .env file, and the error display should be muted)
                $contentTypeBasedHandler->blacklist('_SERVER', 'DB_PASSWORD');
                $contentTypeBasedHandler->blacklist('_SERVER', 'SMTP_PASSWORD');
                $contentTypeBasedHandler->blacklist('_ENV', 'DB_PASSWORD');
                $contentTypeBasedHandler->blacklist('_ENV', 'SMTP_PASSWORD');
                //$prettyPageHandler->setEditor($settings['whoops.editor']);
                //$prettyPageHandler->setPageTitle($settings['whoops.page_title']);
                break;

            //default:
                // TODO : If an Accept header field is present, and if the server cannot send a response which is acceptable according to the combined Accept field value, then the server SHOULD return a 406 (not acceptable) response.
                //https://github.com/phapi/middleware-content-negotiation/blob/master/src/Phapi/Middleware/ContentNegotiation/FormatNegotiation.php#L83
                // TODO : lever plutot une exception du genre http error 406 Not acceptable
                // TODO : réfléchir à ce cas car cela ne peut pas arriver car si le contentType n'est pas dans la liste définie en constante de classe on renvoit par défaut text/html !!!!!!!!!!
            //    throw new UnexpectedValueException('Cannot render unknown content type ' . $contentType);
        }
        $this->prependHandler($contentTypeBasedHandler);
    }

    /**
     * @param callable|HandlerInterface $handler
     */
    private function prependHandler($handler)
    {
        $existingHandlers = array_merge([$handler], $this->whoops->getHandlers());
        $this->whoops->clearHandlers();
        foreach ($existingHandlers as $existingHandler) {
            $this->whoops->pushHandler($existingHandler);
        }
    }
}
