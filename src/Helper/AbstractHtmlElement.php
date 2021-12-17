<?php

namespace Laminas\View\Helper;

abstract class AbstractHtmlElement extends AbstractHelper
{
    /**
     * EOL character
     *
     * @deprecated just use PHP_EOL
     */
    const EOL = PHP_EOL;

    /**
     * The tag closing bracket
     *
     * @var string
     */
    protected $closingBracket = null;

    /**
     * Get the tag closing bracket
     *
     * @return string
     */
    public function getClosingBracket()
    {
        if (! $this->closingBracket) {
            if ($this->isXhtml()) {
                $this->closingBracket = ' />';
            } else {
                $this->closingBracket = '>';
            }
        }

        return $this->closingBracket;
    }

    /**
     * Is doctype XHTML?
     *
     * @return bool
     */
    protected function isXhtml()
    {
        return $this->getView()->plugin('doctype')->isXhtml();
    }

    /**
     * Converts an associative array to a string of tag attributes.
     *
     * @access public
     *
     * @param array $attribs From this array, each key-value pair is
     * converted to an attribute name and value.
     *
     * @return string The XHTML for the attributes.
     */
    protected function htmlAttribs($attribs)
    {
        foreach ((array) $attribs as $key => $val) {
            if ('id' == $key) {
                $attribs[$key] = $this->normalizeId($val);
            }
        }

        $attribs = $this->getView()->plugin(HtmlAttributes::class)($attribs);

        return (string) $attribs;
    }

    /**
     * Normalize an ID
     *
     * @param  string $value
     * @return string
     */
    protected function normalizeId($value)
    {
        if (false !== strpos($value, '[')) {
            if ('[]' == substr($value, -2)) {
                $value = substr($value, 0, strlen($value) - 2);
            }
            $value = trim($value, ']');
            $value = str_replace('][', '-', $value);
            $value = str_replace('[', '-', $value);
        }

        return $value;
    }
}
