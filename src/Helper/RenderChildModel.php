<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Laminas\View\Exception\RuntimeException;
use Laminas\View\Model\ModelInterface as Model;
use Laminas\View\Renderer\RendererInterface;

use function assert;
use function sprintf;

/**
 * Helper for rendering child view models
 *
 * Finds children matching "capture-to" values, and renders them using the
 * composed view instance.
 */
final readonly class RenderChildModel
{
    public function __construct(
        private ViewModel $viewModelHelper,
        private RendererInterface $renderer,
    ) {
    }

    /**
     * Render the child model identified by $child
     *
     * If a matching child model is found, it is rendered. If not, an empty string is returned.
     *
     * @param non-empty-string $child
     */
    public function __invoke(string $child): string
    {
        $model = $this->findChild($child);
        if ($model === null) {
            return '';
        }

        $current = $this->viewModelHelper->getCurrent();
        assert($current !== null);

        $content = $this->renderer->render($model);

        $this->viewModelHelper->setCurrent($current);

        return $content;
    }

    /**
     * Find the named child model
     *
     * Iterates through the current view model, looking for a child model that
     * has a captureTo value matching the requested $child. If found, that child
     * model is returned; otherwise, a boolean false is returned.
     *
     * @throws RuntimeException If no model is currently present.
     */
    private function findChild(string $child): Model|null
    {
        $model = $this->viewModelHelper->getCurrent();
        if ($model === null) {
            throw new RuntimeException(sprintf(
                '%s: no view model currently registered in renderer; cannot query for children',
                __METHOD__,
            ));
        }

        foreach ($model->getChildren() as $childModel) {
            if ($childModel->captureTo() === $child) {
                return $childModel;
            }
        }

        return null;
    }
}
