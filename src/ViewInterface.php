<?php

declare(strict_types=1);

namespace Laminas\View;

use Laminas\View\Exception\RenderingFailedException;
use Laminas\View\Model\ModelInterface;

interface ViewInterface
{
    /**
     * Render the named template with the given, optional, view variables
     *
     * @param non-empty-string $template
     * @param iterable<non-empty-string, mixed>|null $variables
     * @throws RenderingFailedException If it is not possible to render the template for any reason.
     */
    public function renderTemplate(string $template, iterable|null $variables = null): string;

    /**
     * Recursively render the given View Model
     *
     * Implementations should perform a depth-first rendering of all possibly nested child models and aggregate the
     * output to a string.
     *
     * @throws RenderingFailedException If it is not possible to render the model for any reason.
     */
    public function render(ModelInterface $viewModel): string;
}
