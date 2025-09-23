<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Laminas\View\Model\ModelInterface;
use Laminas\View\Renderer\PhpRenderer;

/**
 * Renders a template and stores the rendered output as a placeholder
 * variable for later use.
 */
final class RenderToPlaceholder
{
    public function __construct(
        private readonly PhpRenderer $renderer,
        private readonly Placeholder $placeholder,
    ) {
    }

    /**
     * Renders a template and stores the rendered output as a placeholder
     * variable for later use.
     *
     * @param non-empty-string|ModelInterface $script The template script to render
     * @param non-empty-string $placeholder The placeholder variable name in which to store the output
     */
    public function __invoke(string|ModelInterface $script, string $placeholder): void
    {
        $this->placeholder->__invoke()->append(
            $this->renderer->render($script),
            $placeholder,
        );
    }
}
