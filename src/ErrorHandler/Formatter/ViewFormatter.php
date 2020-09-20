<?php

declare(strict_types=1);

namespace Chiron\ErrorHandler\Formatter;

use Chiron\Views\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class ViewFormatter extends AbstractFormatter
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
        $statusCode = $this->getErrorStatusCode($e);

        return $this->renderer->exists("errors/{$statusCode}");
    }

    /**
     * Render JSON error.
     *
     * @param \Throwable $e
     *
     * @return string
     */
    public function format(ServerRequestInterface $request, Throwable $exception): string
    {
        $data['status'] = $statusCode = $this->getErrorStatusCode($exception);
        $data['title'] = $this->getErrorTitle($exception);
        $data['detail'] = $this->getErrorDetail($exception);

        // add some context attributes that can be used by the view.
        $data['throwable'] = $exception;
        $data['request'] = $request;

        // TODO : gérer le cas des PDOException pour la BDD, avec un template spécial => https://github.com/cakephp/cakephp/blob/dc63c2f0d8a1e9d5f336ab81b587a54929d9e1cf/src/Error/ExceptionRenderer.php#L335
        //https://github.com/cakephp/cakephp/blob/2341c3cd7c32e315c2d54b625313ef55a86ca9cc/src/Template/Error/pdo_error.ctp
        return $this->renderer->render("errors/{$statusCode}", $data);
    }
}
