<?php

declare(strict_types=1);

namespace Chiron\ErrorHandler\Formatter;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;

//https://github.com/cakephp/cakephp/blob/56f2d2a69870031cd0527d63a2ddeb3fbe6f05d3/src/Utility/Xml.php
//https://github.com/symfony/serializer/blob/master/Encoder/XmlEncoder.php

// ajouter un escape des caractéres XML : https://github.com/symfony/error-renderer/blob/master/ErrorRenderer/XmlErrorRenderer.php#L82

//https://github.com/yiisoft/yii-web/blob/master/src/ErrorHandler/XmlRenderer.php

class XmlFormatter extends AbstractFormatter
{
    /**
     * Get the supported content type.
     *
     * @return string
     */
    public function contentType(): string
    {
        return 'application/xml';
        // TODO : regarder pourquoi cela ne fonctionne pas quand on utilise le mime typz => problem+xml car dans chrome le xml n'est pas affiché :(
        //return 'application/problem+xml';
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
        return true;
    }

    /**
     * Render XML error.
     *
     * @param Throwable $error
     *
     * @return string
     */
    public function format(ServerRequestInterface $request, Throwable $e): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
        $xml .= "\n<error>\n";
        $xml .= $this->addElement('status', (string) $this->getErrorStatusCode($e));
        $xml .= $this->addElement('title', $this->getErrorTitle($e));
        $xml .= $this->addElement('detail', $this->getErrorDetail($e));
        $xml .= '</error>';

        return $xml;
    }

    private function addElement(string $name, string $content): string
    {
        return '  ' . "<$name>" . ($this->needsCdataWrapping($content) ? $this->createCdataSection($content) : $content) . "</$name>\n";
    }

    private function needsCdataWrapping(string $value): bool
    {
        return preg_match('/[<>&]/', $value) > 0;
    }

    private function createCdataSection(string $value): string
    {
        return '<![CDATA[' . str_replace(']]>', ']]]]><![CDATA[>', $value) . ']]>';
    }
}
