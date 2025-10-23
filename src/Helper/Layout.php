<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Laminas\View\Model\ModelInterface;
use Laminas\View\Model\ViewModel;

/**
 * View helper for changing the layout template or retrieving the layout (root) view model
 */
final class Layout implements StatefulHelperInterface
{
    private bool $disabled;
    private ModelInterface|null $model;

    public function __construct()
    {
        $this->disabled = false;
        $this->model    = null;
    }

    public function resetState(): void
    {
        $this->disabled = false;
        $this->model    = null;
    }

    /**
     * Return the layout helper instance, optionally setting the current layout
     *
     * @param null|non-empty-string $template Provide a template name to set that template as the current layout
     */
    public function __invoke(string|null $template = null): self
    {
        if ($template !== null) {
            $this->setLayout($template);
        }

        return $this;
    }

    /**
     * Override the layout template in use for this rendering cycle
     *
     * @param non-empty-string $template
     */
    public function setLayout(string $template): void
    {
        $this->getModel()->setTemplate($template);
    }

    /**
     * Return the current layout template, if it has been set
     *
     * @return non-empty-string|null
     */
    public function getLayoutTemplate(): string|null
    {
        $template = $this->getModel()->getTemplate();

        return $template === '' ? null : $template;
    }

    /**
     * Disable layout for this rendering cycle
     */
    public function disable(): void
    {
        $this->disabled = true;
    }

    /**
     * Whether layout is disabled for this rendering cycle
     */
    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    /**
     * Return the layout model
     *
     * You may need the layout model so that you can set view variables in the layout template for example.
     */
    public function getModel(): ModelInterface
    {
        if (! $this->model instanceof ModelInterface) {
            $this->model = new ViewModel();
        }

        return $this->model;
    }
}
