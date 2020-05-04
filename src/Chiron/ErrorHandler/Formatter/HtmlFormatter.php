<?php

declare(strict_types=1);

namespace Chiron\ErrorHandler\Formatter;

use function file_get_contents;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

// TODO : ajouter un escape des caractéres HTML : https://github.com/symfony/error-renderer/blob/master/ErrorRenderer/HtmlErrorRenderer.php#L318

// TODO : escape des caractéres HTML => https://github.com/symfony/symfony/blob/a44f58bd79296675b93a6bfc1826d85f6bd6acca/src/Symfony/Component/ErrorHandler/ErrorRenderer/HtmlErrorRenderer.php#L183

class HtmlFormatter extends AbstractFormatter
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

    public function format(ServerRequestInterface $request, Throwable $e): string
    {
        $data['status'] = (string) $this->getErrorStatusCode($e);
        $data['title'] = $this->getErrorTitle($e);
        $data['detail'] = $this->getErrorDetail($e);

        return $this->render($data);
    }

    /**
     * Render the page with given data.
     *
     * @param array $data
     *
     * @return string
     */
    private function render(array $data): string
    {
        // TODO : lever une exception si la valeur de retour est === false car cela veut dire qu'on n'a pas réussi à lire le fichier....
        $html = file_get_contents($this->path, false);

        foreach ($data as $key => $val) {
            // TODO : il faudrait utiliser la fonction "h()" pour faire un html encode de la variable $val
            $html = str_replace("{{ $$key }}", $val, $html);
        }

        return $html;
    }
}
