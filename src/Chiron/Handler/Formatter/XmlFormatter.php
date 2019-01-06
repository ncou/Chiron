<?php

declare(strict_types=1);

namespace Chiron\Handler\Formatter;

use Chiron\Handler\ExceptionInfo;
use Chiron\Http\Exception\HttpException;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

//https://github.com/cakephp/cakephp/blob/56f2d2a69870031cd0527d63a2ddeb3fbe6f05d3/src/Utility/Xml.php
//https://github.com/symfony/serializer/blob/master/Encoder/XmlEncoder.php

class XmlFormatter implements FormatterInterface
{
    // Allow the float to keep the zero (ex : 12.0 is converted to "12.0" instead of "12").
    private const XML_PRESERVE_ZERO_FRACTION = true;

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
     * Render XML error.
     *
     * @param Throwable $error
     *
     * @return string
     */
    public function format(ServerRequestInterface $request, Throwable $e): string
    {
        // This class doesn't show debug information, so by default we hide the php exception behind a neutral http 500 error.
        if (! $e instanceof HttpException) {
            $e = new \Chiron\Http\Exception\Server\InternalServerErrorHttpException();
        }


// TODO : c'est un test. A virer !!!!!
        /*
        $info = array_merge($info, ['exception' => $e, " toto /is back<to>". chr(10) ."home baby" => "that 'is' <right>".chr(127), 'pretty' => true, 'ugly' => false, 'money' => 19.0, 'bonus' => 12, 'uri' => "<http:'//www.exémple.com/>", 'unicode' => "\xc3\xa9"]);
*/

        return $this->arrayToXml($e->toArray());

    }

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

    private function arrayToXml(array $data): string
    {
        // Ensure any objects are flattened to arrays first
        // TODO : vérifier l'utilité de ce bout de code !!!!
        $content = json_decode(json_encode($data, JSON_PRESERVE_ZERO_FRACTION), true); // JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION

        // ensure all keys are valid XML can be json_encoded
        $cleanedContent = $this->cleanKeysForXml($content);

        $this->document = new \DOMDocument('1.0', 'UTF-8');
        $root = $this->document->createElement('problem');
        $root->setAttribute('xmlns', 'urn:ietf:rfc:7807');
        $this->document->appendChild($root);

        $this->convertElement($root, $cleanedContent);

        if ($this->pretty) {
            $this->document->preserveWhiteSpace = true;
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




    /**
     * Checks the name is a valid xml element name.
     */
/*
    final protected function isElementNameValid(string $name): bool
    {
        return $name &&
            false === strpos($name, ' ') &&
            preg_match('#^[\pL_][\pL0-9._:-]*$#ui', $name);
    }
*/

    /**
     * Checks if a value contains any characters which would require CDATA wrapping.
     */
    /*
    private function needsCdataWrapping(string $val): bool
    {
        return 0 < preg_match('/[<>&]/', $val);
    }*/

/*
    final protected function appendText(\DOMNode $node, string $val): bool
    {
        $nodeText = $this->dom->createTextNode($val);
        $node->appendChild($nodeText);
        return true;
    }
    final protected function appendCData(\DOMNode $node, string $val): bool
    {
        $nodeText = $this->dom->createCDATASection($val);
        $node->appendChild($nodeText);
        return true;
    }
*/

    /**
     * Tests the value being passed and decide what sort of element to create.
     *
     * @param mixed $val
     *
     * @throws NotEncodableValueException
     */
    /*
    private function selectNodeType(\DOMNode $node, $val): bool
    {
        if (\is_array($val)) {
            return $this->buildXml($node, $val);
        } elseif ($val instanceof \SimpleXMLElement) {
            $child = $this->dom->importNode(dom_import_simplexml($val), true);
            $node->appendChild($child);
        } elseif ($val instanceof \Traversable) {
            $this->buildXml($node, $val);
        } elseif (\is_object($val)) {
            return $this->selectNodeType($node, $this->serializer->normalize($val, $this->format, $this->context));
        } elseif (is_numeric($val)) {
            return $this->appendText($node, (string) $val);
        } elseif (\is_string($val) && $this->needsCdataWrapping($val)) {
            return $this->appendCData($node, $val);
        } elseif (\is_string($val)) {
            return $this->appendText($node, $val);
        } elseif (\is_bool($val)) {
            return $this->appendText($node, (int) $val);
        } elseif ($val instanceof \DOMNode) {
            $child = $this->dom->importNode($val, true);
            $node->appendChild($child);
        }
        return true;
    }*/

}
