<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View\Helper;

use Laminas\View\Helper\Escaper;

/**
 * Helper for escaping values
 *
 * @package    Laminas_View
 * @subpackage Helper
 */
class EscapeHtml extends Escaper\AbstractHelper
{

    /**
     * Escape a value for current escaping strategy
     *
     * @param string $value
     * @return string
     */
    protected function escape($value)
    {
        return $this->getEscaper()->escapeHtml($value);
    }
}
