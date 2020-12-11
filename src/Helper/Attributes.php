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
     * Returns a new AttributeStore, optionally initializing it with the
     * provided value.
     *
     * @param array|AttributeStore $attribs Attributes
     *
     * @return AttributeStore
     */
    public function __invoke($attribs = [])
    {
        if ($attribs instanceof AttributeStore) {
            $attribs = $attribs->getAttributes();
        }
        return new AttributeStore($this, $attribs);
    }

    /**
     * Converts an associative array or AttributeStore object to a string of
     * tag attributes.
     *
     * @param array|AttributeStore $attribs Attributes
     *
     * @return string
     */
    public function createAttributesString($attribs)
    {
        if ($attribs instanceof AttributeStore) {
            $attribs = $attribs->getAttributes();
        }
        return $this->htmlAttribs($attribs);
    }
}
