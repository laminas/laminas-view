<?php

declare(strict_types=1);

namespace Laminas\View\Renderer;

use Laminas\View\Exception\RenderingFailedException;
use Laminas\View\Helper\ViewModel as ViewModelHelper;
use Laminas\View\HelperPluginManagerInterface;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Model\ViewModel;
use Laminas\View\Resolver\ResolverInterface;

use function assert;
use function is_string;

final class PhpRenderer implements RendererInterface
{
    /** @var (callable(string): string)|null */
    private $filter;
    private readonly ViewModelHelper $viewModelHelper;

    public function __construct(
        private readonly HelperPluginManagerInterface $pluginManager,
        private readonly ResolverInterface $templateResolver,
        private readonly bool $strictVariables = true,
    ) {
        $this->viewModelHelper = $this->pluginManager->get(ViewModelHelper::class);
    }

    /**
     * Set a post-rendering filter to apply to the rendered output
     *
     * @param callable(string): string $filter
     */
    public function setFilter(callable $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    /** @inheritDoc */
    public function render(
        string|ModelInterface $templateNameOrModel,
        iterable|null $variables = null,
    ): string {
        $templateName = $templateNameOrModel instanceof ModelInterface
            ? $templateNameOrModel->getTemplate()
            : $templateNameOrModel;

        if ($templateName === '') {
            throw RenderingFailedException::becauseATemplateWasNotSpecified();
        }

        if ($templateNameOrModel instanceof ModelInterface && $variables !== null) {
            throw RenderingFailedException::becauseOfAmbiguousArgumentsToPhpRenderer();
        }

        $viewModel = $templateNameOrModel instanceof ModelInterface
            ? $templateNameOrModel
            : new ViewModel($variables ?? [], $templateName);

        $content = $this->renderModel($viewModel);

        if ($this->filter !== null) {
            return ($this->filter)($content);
        }

        return $content;
    }

    public function renderRecursively(ModelInterface $model): string
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

        return $this->renderModel($model);
    }

    private function renderModel(ModelInterface $model): string
    {
        $template = $model->getTemplate();
        assert($template !== '');

        $filename = $this->templateResolver->resolve($template);
        if ($filename === false) {
            throw RenderingFailedException::becauseTheTemplateCannotBeResolvedToAFile($template);
        }

        $this->viewModelHelper->setCurrent($model);

        return (new Template(
            $filename,
            $model->getVariables(),
            $this->pluginManager,
            $this->strictVariables,
        ))();
    }
}
