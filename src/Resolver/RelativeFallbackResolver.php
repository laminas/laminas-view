<?php

declare(strict_types=1);

namespace Laminas\View\Resolver;

use Laminas\View\Helper\ViewModel;
use Laminas\View\Model\ModelInterface;

use function strrpos;
use function substr;

/**
 * Relative fallback resolver - resolves to view templates in a sub-path of the
 * currently set view model's template.
 *
 * The current view model is retrieved from the ViewModel view helper
 *
 * This allows for usage of partial template paths such as `some/partial`, resolving to
 * `my/module/script/path/some/partial.phtml`, while rendering template `my/module/script/path/my-view`
 */
final class RelativeFallbackResolver implements ResolverInterface
{
    public const NS_SEPARATOR = '/';

    public function __construct(
        private readonly ResolverInterface $resolver,
        private readonly ViewModel $viewModelHelper,
    ) {
    }

    /** @inheritDoc */
    public function resolve(string $name): string|false
    {
        $template = $this->resolveTemplateName($name);
        if ($template === false) {
            return false;
        }

        return $this->resolver->resolve($template);
    }

    /**
     * @param non-empty-string $name
     * @return non-empty-string|false
     */
    private function resolveTemplateName(string $name): string|false
    {
        $currentModel = $this->viewModelHelper->getCurrent();

        if (! $currentModel instanceof ModelInterface) {
            return false;
        }

        $currentTemplate = $currentModel->getTemplate();
        $position        = strrpos($currentTemplate, self::NS_SEPARATOR);

        if ($position === false) {
            return false;
        }

        return substr($currentTemplate, 0, $position) . self::NS_SEPARATOR . $name;
    }
}
