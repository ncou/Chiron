<?php

declare(strict_types=1);

namespace Chiron\Handler\Formatter;

use Chiron\Handler\ExceptionInfo;
use Chiron\Http\Exception\HttpException;
use Throwable;

class XmlFormatter implements FormatterInterface
{
    // Allow the float to keep the zero (ex : 12.0 is converted to "12.0" instead of "12").
    private const XML_PRESERVE_ZERO_FRACTION = true;
    /**
     * The exception info instance.
     *
     * @var \Chiron\Handler\ExceptionInfo
     */
    protected $info;

    /**
     * The root DOM Document.
     *
     * @var DOMDocument
     */
    protected $document;

    /**
     * Pretty format the output xml ?
     *
     * @var bool
     */
    // TODO : initialiser cette valeur via un parametre dans le constructeur.
    protected $pretty = true;

    /**
     * Create a new json displayer instance.
     *
     * @param \Chiron\Handler\ExceptionInfo $info
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
        $info = $this->info->generate($e);

// TODO : c'est un test. A virer !!!!!
        $info = array_merge($info, ['exception' => $e, " toto /is back<to>". chr(10) ."home baby" => "that 'is' <right>".chr(127), 'pretty' => true, 'ugly' => false, 'money' => 19.0, 'bonus' => 12, 'uri' => "<http:'//www.exémple.com/>", 'unicode' => "\xc3\xa9"]);

        return $this->arrayToXml($info, true);

    }

    /**
     * Get the supported content type.
     *
     * @return string
     */
    public function contentType(): string
    {
        return 'application/problem+xml';
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


    private function arrayToXml(array $data)
    {
        // Ensure any objects are flattened to arrays first
        // TODO : vérifier l'utilité de ce bout de code !!!!
        $content = json_decode(json_encode($data, JSON_PRESERVE_ZERO_FRACTION), true);

        // ensure all keys are valid XML can be json_encoded
        $cleanedContent = $this->cleanKeysForXml($content);

        $this->document = new \DOMDocument('1.0', 'UTF-8');
        $root = $this->document->createElement('problem');
        $root->setAttribute('xmlns', 'urn:ietf:rfc:7807');
        $this->document->appendChild($root);

        $this->convertElement($root, $cleanedContent);

        if ($this->pretty) {
            $this->document->preserveWhiteSpace = false;
            $this->document->formatOutput = true;
        }

        return $this->document->saveXML();

    }

    /**
     * Parse individual element.
     *
     * @param DOMElement $element
     * @param mixed $value
     */
    // TODO : renommer en appendXmlChildren
    private function convertElement(\DOMElement $element, $value)
    {
        if (! is_array($value)) {
            $value = htmlspecialchars(static::convertToString($value));
            $element->nodeValue = $value;
            return;
        }

        foreach ($value as $key => $data) {
            $child = $this->document->createElement($key);
            $element->appendChild($child);
            $this->convertElement($child, $data);
        }
    }

    /**
     * Get string representation of boolean value.
     * Always cast the result as a string (in case of integer for example).
     * Keep the zero after the fraction for the float values.
     * String are not modified.
     *
     * @param mixed $v
     *
     * @return string
     */
    private static function convertToString($value): string
    {
        // float value
        echo(var_dump($value).$value);
        if (is_float($value)) {

            $value = (string) $value;
            if (self::XML_PRESERVE_ZERO_FRACTION && strpos($value, '.') === false) {
                $value .= '.0';
            }
        }

        // bool value
        if (is_bool($value)) {
            $value = ($value === true) ? 'true' : 'false';
        }

        // int or string value.
        return (string) $value;
    }

    /**
     * Ensure all keys in this associative array are valid XML tag names by replacing invalid
     * characters with an `_`.
     */
    private function cleanKeysForXml(array $input): array
    {
        $return = [];
        foreach ($input as $key => $value) {
            $key = str_replace(chr(10), '_', $key); // TODO : correctif temporaire par rapport à ce bug : https://github.com/zendframework/zend-problem-details/issues/45
            $startCharacterPattern =
                '[A-Z]|_|[a-z]|[\xC0-\xD6]|[\xD8-\xF6]|[\xF8-\x{2FF}]|[\x{370}-\x{37D}]|[\x{37F}-\x{1FFF}]|'
                . '[\x{200C}-\x{200D}]|[\x{2070}-\x{218F}]|[\x{2C00}-\x{2FEF}]|[\x{3001}-\x{D7FF}]|[\x{F900}-\x{FDCF}]'
                . '|[\x{FDF0}-\x{FFFD}]';
            $characterPattern = $startCharacterPattern . '|\-|\.|[0-9]|\xB7|[\x{300}-\x{36F}]|[\x{203F}-\x{2040}]';
            $key = preg_replace('/(?!'.$characterPattern.')./u', '_', $key);
            $key = preg_replace('/^(?!'.$startCharacterPattern.')./u', '_', $key);
            if (is_array($value)) {
                $value = $this->cleanKeysForXml($value);
            }
            $return[$key] = $value;
        }
        return $return;
    }
}
