<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Laminas\Escaper\EscaperInterface;
use Laminas\View\HtmlAttributesSet;

use function array_keys;
use function array_map;
use function array_merge;
use function array_values;
use function implode;
use function sprintf;

use const PHP_EOL;

final readonly class HtmlObject
{
    public function __construct(
        private EscaperInterface $escaper,
        private Doctype $doctype,
    ) {
    }

    /**
     * Output an object set
     *
     * @param string $data The data attribute - the URL of the resource
     * @param string $type The mime type of the target resource
     * @param array<string, scalar> $attributes HTML tag attributes
     * @param array<string, scalar> $params Parameters for the resource
     * @param string|null $content Fallback content. This content is not escaped and is assumed to be markup.
     */
    public function __invoke(
        string $data,
        string $type,
        array $attributes = [],
        array $params = [],
        string|null $content = null,
    ): string {
        $attributes = array_merge(['data' => $data, 'type' => $type], $attributes);
        $parameters = implode(PHP_EOL, array_map(
            fn(string $name, int|float|bool|string $value): string => sprintf(
                '    <param name="%s" value="%s"%s>',
                $this->escaper->escapeHtmlAttr($name),
                $this->escaper->escapeHtmlAttr((string) $value),
                $this->doctype->isXhtml() ? ' /' : '',
            ),
            array_keys($params),
            array_values($params),
        ));

        $attributes = (string) new HtmlAttributesSet($this->escaper, $attributes);

        return <<<HTML
            <object{$attributes}>
            {$parameters}
                {$content}
            </object>
            HTML;
    }
}
