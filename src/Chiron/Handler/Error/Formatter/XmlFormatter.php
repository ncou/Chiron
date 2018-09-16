<?php

declare(strict_types=1);

namespace Chiron\Handler\Error\Formatter;

use Chiron\Handler\Error\ExceptionInfo;
use Chiron\Http\Exception\HttpExceptionInterface;
use Throwable;

class XmlFormatter implements ExceptionFormatterInterface
{
    /**
     * The exception info instance.
     *
     * @var \Chiron\Handler\Error\ExceptionInfo
     */
    protected $info;

    /**
     * Create a new json displayer instance.
     *
     * @param \Chiron\Handler\Error\ExceptionInfo $info
     */
    public function __construct(ExceptionInfo $info)
    {
        $this->info = $info;
    }

    /**
     * Render XML error.
     *
     * @param Throwable $error
     *
     * @return string
     */
    public function format(Throwable $e): string
    {
        $code = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
        $info = $this->info->generate($e, $code);

        // TODO : virer ce header !!!!!!
        /*
        $xml = "<?xml version='1.0' encoding='UTF-8'?>\n";
        */
        $xml = '';
        $xml .= "<errors>\n";
        $xml .= "  <error>\n";
        $xml .= '    <status>' . $info['code'] . "</status>\n";
        $xml .= '    <title>' . $this->createCdataSection($info['name']) . "</title>\n";
        $xml .= '    <detail>' . $this->createCdataSection($info['detail']) . "</detail>\n";
        $xml .= "  </error>\n";
        $xml .= '</errors>';

        return $xml;
    }

    /**
     * Returns a CDATA section with the given content.
     *
     * @param string $content
     *
     * @return string
     */
    private function createCdataSection(string $content): string
    {
        return sprintf('<![CDATA[%s]]>', str_replace(']]>', ']]]]><![CDATA[>', $content));
    }

    /**
     * Get the supported content type.
     *
     * @return string
     */
    public function contentType(): string
    {
        return 'application/xml';
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
}
