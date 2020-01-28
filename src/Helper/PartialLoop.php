<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View\Helper;

use Traversable;
use Laminas\Stdlib\ArrayUtils;
use Laminas\View\Exception;

/**
 * Helper for rendering a template fragment in its own variable scope; iterates
 * over data provided and renders for each iteration.
 */
class PartialLoop extends Partial
{
    /**
     * Marker to where the pointer is at in the loop
     *
     * @var int
     */
    protected $partialCounter = 0;

    /**
     * Renders a template fragment within a variable scope distinct from the
     * calling View object.
     *
     * If no arguments are provided, returns object instance.
     *
     * @param  string $name   Name of view script
     * @param  array  $values Variables to populate in the view
     * @throws Exception\InvalidArgumentException
     * @return string
     */
    public function __invoke($name = null, $values = null)
    {
        if (0 == func_num_args()) {
            return $this;
        }

        if (!is_array($values)) {
            if ($values instanceof Traversable) {
                $values = ArrayUtils::iteratorToArray($values);
            } elseif (is_object($values) && method_exists($values, 'toArray')) {
                $values = $values->toArray();
            } else {
                throw new Exception\InvalidArgumentException('PartialLoop helper requires iterable data');
            }
        }

        // reset the counter if it's called again
        $this->partialCounter = 0;
        $content = '';

        foreach ($values as $item) {
            $this->partialCounter++;
            $content .= parent::__invoke($name, $item);
        }

        return $content;
    }

    /**
     * Get the partial counter
     *
     * @return int
     */
    public function getPartialCounter()
    {
        return $this->partialCounter;
    }
}
