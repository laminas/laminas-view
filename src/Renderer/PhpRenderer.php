<?php

declare(strict_types=1);

namespace Laminas\View\Renderer;

use Laminas\View\Exception\RenderingFailedException;
use Laminas\View\Helper\ViewModel;
use Laminas\View\HelperPluginManagerInterface;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Resolver\ResolverInterface;

use function is_array;
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
        /**
         * Make sure that a template file path can be resolved:
         */
        $templateName = $templateNameOrModel instanceof ModelInterface
            ? $templateNameOrModel->getTemplate()
            : $templateNameOrModel;

        if ($templateName === '') {
            throw RenderingFailedException::becauseATemplateWasNotSpecified();
        }

        $filename = $this->templateResolver->resolve($templateName);
        if ($filename === false) {
            throw RenderingFailedException::becauseTheTemplateCannotBeResolvedToAFile($templateName);
        }

        /**
         * Normalise view variables to array<string, mixed>
         */

        // Given a model instance, its variables take precedence over the $values argument
        $variables = $templateNameOrModel instanceof ModelInterface
            ? $templateNameOrModel->getVariables()
            : $variables ?? [];

        $variables = is_array($variables) ? $variables : iterator_to_array($variables);

        if ($templateNameOrModel instanceof ModelInterface) {
            $helper = $this->pluginManager->get(ViewModel::class);
            $helper->setCurrent($templateNameOrModel);
        }

        $content = (new Template(
            $filename,
            $variables,
            $this->pluginManager,
            $this->strictVariables,
        ))();

        if ($this->filter !== null) {
            return ($this->filter)($content);
        }

        return $content;
    }
}
