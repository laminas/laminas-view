<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View\Helper;

/**
 * Helper for processing HTML tag attributes
 */
class Attributes extends AbstractHelper
{
    use AttributesTrait;

    /**
     * Converts an associative array to a string of tag attributes.
     *
     * @param array $attribs From this array, each key-value pair is
     * converted to an attribute name and value.
     *
     * @return string The XHTML for the attributes.
     */
    public function __invoke(array $attribs)
    {
        return $this->htmlAttribs($attribs);
    }
}
