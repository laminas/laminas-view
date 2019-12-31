<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View\Helper;

use Laminas\View\Exception;
use Traversable;

/**
 * Helper for rendering a template fragment in its own variable scope; iterates
 * over data provided and renders for each iteration.
 *
 * @package    Laminas_View
 * @subpackage Helper
 */
class PartialLoop extends Partial
{

    /**
     * Marker to where the pointer is at in the loop
     * @var integer
     */
    protected $partialCounter = 0;

    /**
     * Renders a template fragment within a variable scope distinct from the
     * calling View object.
     *
     * If no arguments are provided, returns object instance.
     *
     * @param  string $name Name of view script
     * @param  array $model Variables to populate in the view
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    public function __invoke($name = null, $model = null)
    {
        if (0 == func_num_args()) {
            return $this;
        }

        if (!is_array($model)
            && (!$model instanceof Traversable)
            && (is_object($model) && !method_exists($model, 'toArray'))
        ) {
            throw new Exception\InvalidArgumentException('PartialLoop helper requires iterable data');
        }

        if (is_object($model)
            && (!$model instanceof Traversable)
            && method_exists($model, 'toArray')
        ) {
            $model = $model->toArray();
        }

        $content = '';
        // reset the counter if it's called again
        $this->partialCounter = 0;
        foreach ($model as $item) {
            // increment the counter variable
            $this->partialCounter++;

            $content .= parent::__invoke($name, $item);
        }

        return $content;
    }
}
