<?php

declare(strict_types=1);

namespace Laminas\View\Model;

/**
 * Interface describing methods for clearing the state of a view model.
 *
 * View models implementing this interface allow clearing children, options,
 * and variables.
 */
interface ClearableModelInterface
{
    /** @return self */
    public function clearChildren();

    /**
     * @deprecated Since 2.40.0 Options never had a use-case for view models and will be removed in 3.0
     *
     * @return self
     */
    public function clearOptions();

    /** @return self */
    public function clearVariables();
}
