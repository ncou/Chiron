<?php

declare(strict_types=1);

namespace Chiron\Handler\Formatter;

use Chiron\Handler\ExceptionInfo;
use Chiron\Http\Exception\HttpException;
use Chiron\Views\TemplateRendererInterface;
use Throwable;

class ViewFormatter implements FormatterInterface
{
    /**
     * The exception info instance.
     *
     * @var \Chiron\Handler\ExceptionInfo
     */
    protected $info;

    /**
     * The renderer instance.
     *
     * @var \Chiron\Views\TemplateRendererInterface
     */
    protected $renderer;

    /**
     * Create a new json displayer instance.
     *
     * @param \Chiron\Handler\ExceptionInfo $info
     */
    public function __construct(ExceptionInfo $info, TemplateRendererInterface $renderer)
    {
        $this->info = $info;
        $this->renderer = $renderer;
    }

    /**
     * Render JSON error.
     *
     * @param \Throwable $e
     *
     * @return string
     */
    public function format(Throwable $e): string
    {
        $info = $this->info->generate($e);
        $info = array_merge($info, ['exception' => $e]); // TODO : vérifier qu'on accéde bien aux informations ajoutées en attribut !!!!!!!!!!!!!

        $code = $info['code'];

        return $this->renderer->render("errors/{$code}", $info);
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
        $code = $e instanceof HttpException ? $e->getStatusCode() : 500;

        return $this->renderer->exists("errors/{$code}");
    }
}
