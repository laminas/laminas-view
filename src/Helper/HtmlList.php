<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Laminas\View\Exception;

use function is_array;
use function sprintf;
use function strlen;
use function substr;

use const PHP_EOL;

/**
 * Helper for ordered and unordered lists
 */
class HtmlList extends AbstractHtmlElement
{
    /**
     * Generates a 'List' element.
     *
     * @param  array $items   Array with the elements of the list
     * @param  bool  $ordered Specifies ordered/unordered list; default unordered
     * @param  array $attribs Attributes for the ol/ul tag.
     * @param  bool  $escape  Escape the items.
     * @throws Exception\InvalidArgumentException
     * @return string The list XHTML.
     */
    public function __invoke(array $items, $ordered = false, $attribs = false, $escape = true)
    {
        if (empty($items)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '$items array can not be empty in %s',
                __METHOD__
            ));
        }

        $list = '';

        foreach ($items as $item) {
            if (! is_array($item)) {
                if ($escape) {
                    $escaper = $this->getView()->plugin('escapeHtml');
                    $item    = $escaper($item);
                }
                $list .= '<li>' . $item . '</li>' . PHP_EOL;
            } else {
                $itemLength = 5 + strlen(PHP_EOL);
                if ($itemLength < strlen($list)) {
                    $list = substr($list, 0, strlen($list) - $itemLength)
                     . $this($item, $ordered, $attribs, $escape) . '</li>' . PHP_EOL;
                } else {
                    $list .= '<li>' . $this($item, $ordered, $attribs, $escape) . '</li>' . PHP_EOL;
                }
            }
        }

        if ($attribs) {
            $attribs = $this->htmlAttribs($attribs);
        } else {
            $attribs = '';
        }

        $tag = $ordered ? 'ol' : 'ul';

        return '<' . $tag . $attribs . '>' . PHP_EOL . $list . '</' . $tag . '>' . PHP_EOL;
    }
}
