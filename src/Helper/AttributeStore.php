<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View\Helper;

/**
 * Utility class for processing HTML tag attributes
 */
class AttributeStore
{
    /**
     * Attributes view helper.
     *
     * @var Attributes
     */
    protected $helper;

    /**
     * Array of tag attributes.
     *
     * @var array
     */
    protected $attribs;

    /**
     * AttributeStore constructor.
     *
     * @param Attributes $helper Attributes view helper
     * @param array $attribs Array of tag attributes
     */
    public function __construct(Attributes $helper, array $attribs = [])
    {
        $this->helper = $helper;
        $this->setAttributes($attribs);
    }

    /**
     * Returns a string of all attribute/value pairs in the store.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->helper->createAttributesString($this);
    }

    /**
     * Returns the attributes array.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attribs;
    }

    /**
     * Sets (replaces) the attributes array.
     *
     * @param $attribs array Array of tag attributes
     *
     * @return $this
     */
    public function setAttributes(array $attribs)
    {
        $this->attribs = $attribs;
        return $this;
    }

    /**
     * Sets the value for a specific attribute in the store.
     *
     * @param $attrib string Attribute
     * @param $value string|array Value
     *
     * @return $this
     */
    public function setAttribute($attrib, $value)
    {
        $this->attribs[$attrib] = $value;
        return $this;
    }

    /**
     * Adds a value to a specific attribute in the store.
     *
     * Sets the attribute if it does not exist.
     *
     * @param $attrib string Attribute
     * @param $value string|array Value
     *
     * @return $this
     */
    public function addAttributeValue($attrib, $value)
    {
        if ($this->hasAttribute($attrib)) {
            $this->attribs[$attrib] = array_merge((array)$this->attribs[$attrib], (array)$value);
        }
        else {
            $this->setAttribute($attrib, $value);
        }
        return $this;
    }

    /**
     * Adds (merges) attributes to the store.
     *
     * @param $attribs array Array of tag attributes
     *
     * @return $this
     */
    public function addAttributes($attribs)
    {
        $this->attribs = array_merge_recursive($this->attribs, $attribs);
        return $this;
    }

    /**
     * Does the store contain a specific attribute?
     *
     * @param $attrib string Attribute
     *
     * @return bool
     */
    public function hasAttribute($attrib)
    {
        return array_key_exists($attrib, $this->attribs);
    }

    /**
     * Does the store contain a specific attribute and value?
     *
     * @param $attrib string Attribute
     * @param $value string Value
     *
     * @return bool
     */
    public function hasAttributeValue($attrib, $value)
    {
        if ($this->hasAttribute($attrib)) {
            if (is_array($this->attribs[$attrib])) {
                return in_array($value, $this->attribs[$attrib]);
            }
            else {
                return $value === $this->attribs[$attrib];
            }
        }
        return false;
    }
}
