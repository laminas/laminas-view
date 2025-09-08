<?php

declare(strict_types=1);

namespace Laminas\View;

use Laminas\View\Exception\RenderingFailedException;
use Laminas\View\Helper\ViewModel as ViewModelHelper;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\RendererInterface;

use function is_string;

final class View
{
    private readonly ViewModelHelper $viewModelHelper;

    /**
     * @param non-empty-string $defaultLayoutTemplate
     * @param non-empty-string $defaultCaptureTo
     */
    public function __construct(
        private readonly RendererInterface $renderer,
        private readonly HelperPluginManagerInterface $pluginManager,
        private readonly string $defaultLayoutTemplate,
        private readonly string $defaultCaptureTo,
    ) {
        $this->viewModelHelper = $this->pluginManager->get(ViewModelHelper::class);
    }

    /**
     * Render a configured top-level layout view model
     *
     * It is expected that the given model will have a non-empty template configured and all necessary variables and
     * child models set.
     *
     * @throws RenderingFailedException When any exception occurs during render.
     */
    public function renderLayout(ModelInterface $layout): string
    {
        $this->viewModelHelper->setRoot($layout);
        $content = $this->renderRecursively($layout);
        $this->pluginManager->resetState();

        return $content;
    }

    /**
     * @param non-empty-string|ModelInterface $modelOrTemplate
     * @param iterable<non-empty-string, mixed>|null|ModelInterface $variables
     * @throws RenderingFailedException When any exception occurs during render.
     */
    public function render(
        string|ModelInterface $modelOrTemplate,
        iterable|ModelInterface|null $variables = null,
        bool $enableLayout = true,
    ): string {
        if (is_string($modelOrTemplate)) {
            $model = $variables instanceof ModelInterface
                ? $variables
                : new ViewModel($variables ?? []);
            $model->setTemplate($modelOrTemplate);
        } else {
            $model = $modelOrTemplate;
        }

        if ($enableLayout && $model->terminate() !== true) {
            $layoutModel = new ViewModel([]);
            $layoutModel->setTemplate($this->defaultLayoutTemplate);
            $model->setCaptureTo($this->defaultCaptureTo);
            $layoutModel->addChild($model);

            return $this->renderLayout($layoutModel);
        }

        $content = $this->renderRecursively($model);
        $this->pluginManager->resetState();

        return $content;
    }

    /** @throws RenderingFailedException When any exception occurs during render. */
    private function renderRecursively(ModelInterface $model): string
    {
        foreach ($model->getChildren() as $child) {
            $this->viewModelHelper->setCurrent($child);
            $content = $this->renderRecursively($child);
            if ($child->isAppend()) {
                /** @psalm-var mixed $existingContent */
                $existingContent = $model->getVariable($child->captureTo(), '');
                $existingContent = is_string($existingContent)
                    ? $existingContent
                    : '';

                $content = $existingContent . $content;
            }

            $model->setVariable($child->captureTo(), $content);
        }

        $this->viewModelHelper->setCurrent($model);

        return $this->renderer->render($model);
    }
}
