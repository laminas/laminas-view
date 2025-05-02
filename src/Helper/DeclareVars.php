<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Laminas\View\Renderer\PhpRenderer;

use function is_array;
use function is_string;

/**
 * Helper for declaring default values of template variables
 *
 * This helper is specific to the PhpRenderer
 */
final class DeclareVars
{
    public function __construct(private readonly PhpRenderer $renderer)
    {
    }

    /**
     * Declare template vars to set default values and avoid notices when using strictVars
     *
     * Primarily for use when using {@link \Laminas\View\Variables::setStrictVars()},
     * this helper can be used to declare template variables that may or may
     * not already be set in the view object, as well as to set default values.
     * Arrays passed as arguments to the method will be used to set default
     * values; otherwise, if the variable does not exist, it is set to an empty
     * string.
     *
     * Usage:
     * <code>
     * $this->declareVars(
     *     'varName1',
     *     'varName2',
     *     array('varName3' => 'defaultValue',
     *           'varName4' => array()
     *     )
     * );
     * </code>
     *
     * phpcs:ignore
     * @param string|array ...$arguments variable number of arguments, all string names of variables to test
     */
    public function __invoke(string|array ...$arguments): void
    {
        foreach ($arguments as $key) {
            if (is_array($key)) {
                /** @psalm-var mixed $value */
                foreach ($key as $name => $value) {
                    if (! is_string($name)) {
                        continue;
                    }

                    $this->declareVar($name, $value);
                }

                return;
            }

            $this->declareVar($key);
        }
    }

    /**
     * Set a view variable
     *
     * Checks to see if a $key is set in the view object; if not, sets it to $value with a default of an empty string.
     */
    private function declareVar(string $name, mixed $value = ''): void
    {
        $variables = $this->renderer->vars();

        if (! isset($variables->$name)) {
            $variables->$name = $value;
        }
    }
}
