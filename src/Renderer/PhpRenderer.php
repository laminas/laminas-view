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
use function is_object;
use function iterator_to_array;

final class PhpRenderer implements RendererInterface
{
    /** @var (callable(string): string)|null */
    private $filter;

    public function __construct(
        private readonly HelperPluginManagerInterface $pluginManager,
        private readonly ResolverInterface $templateResolver,
        private readonly bool $strictVariables = true,
    ) {
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

        $variablesEmpty = (is_object($variables) ? iterator_to_array($variables, false) : $variables) === []
            || $variables === null;

        if ($templateNameOrModel instanceof ModelInterface && ! $variablesEmpty) {
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

    private function renderModel(ModelInterface $model): string
    {
        $template = $model->getTemplate();
        assert($template !== '');

        $filename = $this->templateResolver->resolve($template);
        if ($filename === false) {
            throw RenderingFailedException::becauseTheTemplateCannotBeResolvedToAFile($template);
        }

        $helper = $this->pluginManager->get(ViewModelHelper::class);
        $helper->setCurrent($model);

        return (new Template(
            $filename,
            $model->getVariables(),
            $this->pluginManager,
            $this->strictVariables,
        ))();
    }
}
