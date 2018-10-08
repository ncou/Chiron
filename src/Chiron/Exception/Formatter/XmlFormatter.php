<?php

declare(strict_types=1);

namespace Chiron\Exception\Formatter;

use Chiron\Exception\ExceptionInfo;
use Chiron\Http\Exception\HttpException;
use Throwable;

class XmlFormatter implements FormatterInterface
{
    /**
     * The exception info instance.
     *
     * @var \Chiron\Exception\ExceptionInfo
     */
    protected $info;

    /**
     * Create a new json displayer instance.
     *
     * @param \Chiron\Exception\ExceptionInfo $info
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
        $code = $e instanceof HttpException ? $e->getStatusCode() : 500;
        $info = $this->info->generate($e, $code);

        $xml = "<?xml version='1.0' encoding='utf-8'?>\n";
        $xml .= "<error>\n";
        $xml .= '  <status>' . $info['code'] . "</status>\n";
        $xml .= '  <title>' . $this->createCdataSection($info['name']) . "</title>\n";
        $xml .= '  <detail>' . $this->createCdataSection($info['detail']) . "</detail>\n";
        $xml .= '</error>';

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
