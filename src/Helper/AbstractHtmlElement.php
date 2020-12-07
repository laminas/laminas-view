<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View\Helper;

abstract class AbstractHtmlElement extends AbstractHelper
{
    use AttributesTrait;

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
}
