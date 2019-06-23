<?php

declare(strict_types=1);

namespace Chiron\Handler\Formatter;

use Chiron\Http\Exception\HttpException;
use function file_get_contents;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class HtmlFormatter implements FormatterInterface
{
    /**
     * The html template file path.
     *
     * @var string
     */
    protected $path;

    /**
     * Create a new html displayer instance.
     *
     * @param string $path
     */
    // TODO : renommer en $filePath ou $fileName pour le paramétre ????
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function format(ServerRequestInterface $request, Throwable $e): string
    {
        // This class doesn't show debug information, so by default we hide the php exception behind a neutral http 500 error.
        if (! $e instanceof HttpException) {
            $e = new \Chiron\Http\Exception\Server\InternalServerErrorHttpException();
        }

        return $this->arrayToHtml($e->toArray());
    }

    /**
     * Render the page with given data.
     *
     * @param array $data
     *
     * @return string
     */
    private function arrayToHtml(array $data): string
    {
        // TODO : lever une exception si la valeur de retour est === false car cela veut dire qu'on n'a pas réussi à lire le fichier....
        $html = file_get_contents($this->path, false);

        foreach ($data as $key => $val) {
            $html = str_replace("{{ $$key }}", $val, $html);
        }

        return $html;
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
        //TODO : faire une vérifiecation si le fichier existe, c'est à dire tester le $this->path
        return true;
    }
}
