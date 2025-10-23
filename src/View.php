<?php

declare(strict_types=1);

namespace Laminas\View;

use Laminas\View\Helper\Layout;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\RendererInterface;

use function assert;

final readonly class View implements ViewInterface
{
    private Layout $layoutHelper;

    /**
     * @param non-empty-string|null $defaultLayoutTemplate
     * @param non-empty-string $defaultCaptureTo
     */
    public function __construct(
        private RendererInterface $renderer,
        private HelperPluginManagerInterface $pluginManager,
        private string|null $defaultLayoutTemplate,
        private string $defaultCaptureTo,
    ) {
        $this->layoutHelper = $this->pluginManager->get(Layout::class);
    }

    public function renderTemplate(string $template, iterable|null $variables = null): string
    {
        return $this->render(new ViewModel($variables ?? [], $template));
    }

    public function render(ModelInterface $viewModel): string
    {
        $content = $this->renderer->renderRecursively($viewModel);

        if (! $this->isLayoutEnabled($viewModel)) {
            $this->pluginManager->resetState();

            return $content;
        }

        $template = $this->layoutHelper->getLayoutTemplate() ?? $this->defaultLayoutTemplate;
        assert($template !== null);

        $layoutModel = $this->layoutHelper->getModel();
        $layoutModel->setTemplate($template);
        $layoutModel->setVariable($this->defaultCaptureTo, $content);
        $content = $this->renderer->render($layoutModel);
        $this->pluginManager->resetState();

        return $content;
    }

    private function isLayoutEnabled(ModelInterface $contentModel): bool
    {
        /** View models marked as terminal should not be wrapped with a layout */
        if ($contentModel->terminate()) {
            return false;
        }

        /** This indicates that the user has disabled layout from the template context */
        if ($this->layoutHelper->isDisabled()) {
            return false;
        }

        /** If a template can be resolved, layout is enabled */
        $template = $this->defaultLayoutTemplate ?? $this->layoutHelper->getLayoutTemplate();

        return $template !== null;
    }
}
