<?php

declare(strict_types=1);

namespace Laminas\View\Renderer;

use Laminas\View\Exception\RenderingFailedException;
use Laminas\View\Model\ModelInterface;

interface RendererInterface
{
    /**
     * Processes a view script and returns the output.
     *
     * @param non-empty-string|ModelInterface $templateNameOrModel Either the name of a template to render (Not a path)
     *                                                             or a view model (Referencing a template name)
     * @param iterable<non-empty-string, mixed>|null $variables    Variables to use during rendering, if a model is not
     *                                                             passed as the first argument.
     * @return string The rendered output
     * @throws RenderingFailedException When any issue occurs during rendering.
     */
    public function render(
        string|ModelInterface $templateNameOrModel,
        iterable|null $variables = null,
    ): string;

    /** @throws RenderingFailedException When any issue occurs during render. */
    public function renderRecursively(ModelInterface $model): string;
}
