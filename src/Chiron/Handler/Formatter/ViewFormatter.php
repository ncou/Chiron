<?php

declare(strict_types=1);

namespace Chiron\Handler\Formatter;

use Chiron\Http\Exception\HttpException;
use Chiron\Views\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use function array_merge;

class ViewFormatter implements FormatterInterface
{
    /**
     * The renderer instance.
     *
     * @var \Chiron\Views\TemplateRendererInterface
     */
    protected $renderer;

    /**
     * Create a new json displayer instance.
     *
     * @param TemplateRendererInterface $renderer
     */
    public function __construct(TemplateRendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Render JSON error.
     *
     * @param \Throwable $e
     *
     * @return string
     */
    public function format(ServerRequestInterface $request, Throwable $e): string
    {
        // This class doesn't show debug information, so by default we hide the php exception behind a neutral http 500 error.
        if (! $e instanceof HttpException) {
            $e = new \Chiron\Http\Exception\Server\InternalServerErrorHttpException();
        }

        $info = $e->toArray();
        // TODO : ajouter plus d'information dans ce tableau qui va être passé à la vue pour pouvoir utiliser ces informations => https://github.com/cakephp/cakephp/blob/dc63c2f0d8a1e9d5f336ab81b587a54929d9e1cf/src/Error/ExceptionRenderer.php#L218
        /*
            Arguments à passer à la vue :
            $templateData = [
                'response' => $response,
                'request'  => $request,
                'uri'      => (string) $request->getUri(),
                'status'   => $response->getStatusCode(),
                'reason'   => $response->getReasonPhrase(),
                'debug'   => $this->debug,
            ];
            if ($this->debug) {
                $templateData['error'] = $e;
            }
        ]*/

        $info = array_merge($info, ['exception' => $e]); // TODO : vérifier qu'on accéde bien aux informations ajoutées en attribut !!!!!!!!!!!!!

        $statusCode = $info['status'];

        // TODO : gérer le cas des PDOException pour la BDD, avec un template spécial => https://github.com/cakephp/cakephp/blob/dc63c2f0d8a1e9d5f336ab81b587a54929d9e1cf/src/Error/ExceptionRenderer.php#L335
        //https://github.com/cakephp/cakephp/blob/2341c3cd7c32e315c2d54b625313ef55a86ca9cc/src/Template/Error/pdo_error.ctp
        return $this->renderer->render("errors/{$statusCode}", $info);
    }

    /**
     * Get the supported content type.
     *
     * @return string
     */
    public function contentType(): string
    {
        return 'text/html';
    }

    /**
     * Do we provide verbose information about the exception?
     *
     * @return bool
     */
    public function isVerbose(): bool
    {
        return false;
    }

    /**
     * Can we format the exception?
     *
     * @param \Throwable $e
     *
     * @return bool
     */
    public function canFormat(Throwable $e): bool
    {
        $statusCode = $e instanceof HttpException ? $e->getStatusCode() : 500;

        return $this->renderer->exists("errors/{$statusCode}");
    }
}
