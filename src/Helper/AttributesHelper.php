<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View\Helper;

use Laminas\View\Attributes;
use Traversable;

/**
 * Helper for creating Attributes objects
 */
class AttributesHelper extends AbstractHelper
{
    /**
     * Returns a new Attributes object, optionally initializing it with the
     * provided value.
     *
     * @param array|Traversable $attribs Attributes
     *
     * @return Attributes
     */
    public function __invoke($attribs = [])
    {
        return new Attributes(
            $this->getView()->plugin('escapehtml')->getEscaper(),
            $this->getView()->plugin('escapehtmlattr')->getEscaper(),
            $attribs
        );
    }
}
