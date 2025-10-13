<?php

declare(strict_types=1);

namespace Laminas\View;

use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\Asset;
use Laminas\View\Helper\BasePath;
use Laminas\View\Helper\Cycle;
use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\Escaper\AbstractHelper;
use Laminas\View\Helper\GravatarImage;
use Laminas\View\Helper\HeadLink;
use Laminas\View\Helper\HeadMeta;
use Laminas\View\Helper\HeadScript;
use Laminas\View\Helper\HeadStyle;
use Laminas\View\Helper\HeadTitle;
use Laminas\View\Helper\HtmlList;
use Laminas\View\Helper\HtmlObject;
use Laminas\View\Helper\HtmlTag;
use Laminas\View\Helper\InlineScript;
use Laminas\View\Helper\Layout;
use Laminas\View\Helper\Partial;
use Laminas\View\Helper\PartialLoop;
use Laminas\View\Helper\Placeholder;
use Laminas\View\Helper\Placeholder\Position;
use Laminas\View\Helper\RenderChildModel;
use Laminas\View\Helper\ViewModel;
use Laminas\View\Model\ModelInterface;
use Stringable;

/**
 * This interface is used only for the purposes of auto-completion in your IDE
 *
 * You should not implement this interface, and as such, it will not retain backwards compatibility
 *
 * Feel free to write interfaces in your own projects that document helper signatures and extend from this interface.
 * It will be kept up-to-date with the shipped view helpers and their relevant signatures.
 *
 * @psalm-api
 * @psalm-import-type AttributeSet from HtmlAttributesSet
 */
interface TemplateInterface
{
    /**
     * @see Asset
     *
     * @param non-empty-string $asset
     * @return non-empty-string
     * @throws Exception\InvalidArgumentException
     */
    public function asset(string $asset): string;

    /**
     * Returns site's base path, or file with base path prepended.
     *
     * $file is appended to the base path for simplicity.
     *
     * @see BasePath
     *
     * @throws RuntimeException
     */
    public function basePath(string|null $file = null): string;

    /**
     * Add elements to alternate
     *
     * @see Cycle
     *
     * @param list<scalar|Stringable> $data
     */
    public function cycle(array $data = [], string $name = 'default'): Cycle;

    /** @see Doctype */
    public function doctype(): Doctype;

    /**
     * @see AbstractHelper
     *
     * @param int-mask-of<AbstractHelper::RECURSE_*> $recurse Expects one of the recursion constants;
     *                                              used to decide whether to recurse the given value when escaping
     * @throws Exception\InvalidArgumentException
     * @return mixed Given a scalar, a scalar value is returned. Given an object, with the $recurse flag not
     *               allowing object recursion, returns a string. Otherwise, returns an array.
     */
    public function escapeCss(mixed $value, int $recurse = AbstractHelper::RECURSE_NONE): mixed;

    /**
     * @see AbstractHelper
     *
     * @param int-mask-of<AbstractHelper::RECURSE_*> $recurse Expects one of the recursion constants;
     *                                              used to decide whether to recurse the given value when escaping
     * @throws Exception\InvalidArgumentException
     * @return mixed Given a scalar, a scalar value is returned. Given an object, with the $recurse flag not
     *               allowing object recursion, returns a string. Otherwise, returns an array.
     */
    public function escapeHtml(mixed $value, int $recurse = AbstractHelper::RECURSE_NONE): mixed;

    /**
     * @see AbstractHelper
     *
     * @param int-mask-of<AbstractHelper::RECURSE_*> $recurse Expects one of the recursion constants;
     *                                              used to decide whether to recurse the given value when escaping
     * @throws Exception\InvalidArgumentException
     * @return mixed Given a scalar, a scalar value is returned. Given an object, with the $recurse flag not
     *               allowing object recursion, returns a string. Otherwise, returns an array.
     */
    public function escapeHtmlAttr(mixed $value, int $recurse = AbstractHelper::RECURSE_NONE): mixed;

    /**
     * @see AbstractHelper
     *
     * @param int-mask-of<AbstractHelper::RECURSE_*> $recurse Expects one of the recursion constants;
     *                                              used to decide whether to recurse the given value when escaping
     * @throws Exception\InvalidArgumentException
     * @return mixed Given a scalar, a scalar value is returned. Given an object, with the $recurse flag not
     *               allowing object recursion, returns a string. Otherwise, returns an array.
     */
    public function escapeJs(mixed $value, int $recurse = AbstractHelper::RECURSE_NONE): mixed;

    /**
     * @see AbstractHelper
     *
     * @param int-mask-of<AbstractHelper::RECURSE_*> $recurse Expects one of the recursion constants;
     *                                              used to decide whether to recurse the given value when escaping
     * @throws Exception\InvalidArgumentException
     * @return mixed Given a scalar, a scalar value is returned. Given an object, with the $recurse flag not
     *               allowing object recursion, returns a string. Otherwise, returns an array.
     */
    public function escapeUrl(mixed $value, int $recurse = AbstractHelper::RECURSE_NONE): mixed;

    /**
     * @see GravatarImage
     *
     * @param non-empty-string                                  $emailAddress
     * @param positive-int                                      $imageSize
     * @param AttributeSet                                      $imageAttributes
     * @param value-of<GravatarImage::DEFAULT_IMAGE_VALUES>|string $defaultImage
     * @param value-of<GravatarImage::RATINGS>                     $rating
     */
    public function gravatarImage(
        string $emailAddress,
        int $imageSize = 80,
        array $imageAttributes = [],
        string $defaultImage = GravatarImage::DEFAULT_MP,
        string $rating = GravatarImage::RATING_G
    ): string;

    /**
     * @see HeadLink
     *
     * @param array<string, scalar>|null $attributes
     */
    public function headLink(array|null $attributes = null): HeadLink;

    /**
     * @see HeadMeta
     *
     * @param array<string, scalar> $attributes
     */
    public function headMeta(
        string|null $name = null,
        string|null $content = null,
        array $attributes = [],
    ): HeadMeta;

    /** @see HeadScript */
    public function headScript(): HeadScript;

    /**
     * @see HeadStyle
     *
     * Returns headStyle helper object; optionally, appends a new style element to the list
     *
     * @param string|null $content CSS to add to a style element
     * @param array<string, scalar> $attributes to apply to the style element
     */
    public function headStyle(
        string|null $content = null,
        array $attributes = [],
        Position $position = Position::Append,
    ): HeadStyle;

    /** @see HeadTitle */
    public function headTitle(string|null $title = null): HeadTitle;

    /**
     * Returns a new HtmlAttributesSet object, optionally initializing it with
     * the provided value.
     *
     * @param iterable<string, scalar|array|null> $attributes
     */
    public function htmlAttributes(iterable $attributes = []): HtmlAttributesSet;

    /**
     * Generates a 'List' element.
     *
     * @see HtmlList
     *
     * @param  array<array-key, scalar|array> $items Array with the elements of the list
     * @param  bool                           $ordered Specifies ordered/unordered list; default unordered
     * @param  AttributeSet|null              $attribs Attributes for the ol/ul tag.
     * @param  bool                           $escape Whether to Escape the items.
     * @throws Exception\InvalidArgumentException If $items is empty.
     * @return string The list XHTML.
     */
    public function htmlList(
        array $items,
        bool $ordered = false,
        array|null $attribs = null,
        bool $escape = true,
    ): string;

    /**
     * Output an 'object'
     *
     * @see HtmlObject
     *
     * @param string $data The data attribute - the URL of the resource
     * @param string $type The mime type of the target resource
     * @param array<string, scalar> $attributes HTML tag attributes
     * @param array<string, scalar> $params Parameters for the resource
     * @param string|null $content Fallback content. This content is not escaped and is assumed to be markup.
     */
    public function htmlObject(
        string $data,
        string $type,
        array $attributes = [],
        array $params = [],
        string|null $content = null,
    ): string;

    /**
     * @see HtmlTag
     *
     * @param array<string, scalar> $attributes
     */
    public function __invoke(array $attributes = []): HtmlTag;

    /** @see InlineScript */
    public function inlineScript(): InlineScript;

    /**
     * Set layout template or retrieve "layout" view model
     *
     * If no arguments are given, grabs the "root" or "layout" view model.
     * Otherwise, attempts to set the template for that view model.
     *
     * @see Layout
     *
     * @param null|string $template Providing a template name will set that template as the current layout template
     * @return ($template is null ? ModelInterface : Layout)
     */
    public function layout(string|null $template = null): ModelInterface|Layout;

    /**
     * Renders a template fragment within a variable scope distinct from the
     * calling View object. It proxies to view's render function
     *
     * @see Partial
     *
     * @param  string|ModelInterface|null $name Name of view script, or a view model
     * @param  iterable<string, mixed>|object|null $values Variables to populate in the view
     * @return ($name is null ? Partial : string)
     * @throws RuntimeException
     */
    public function partial(
        string|ModelInterface|null $name = null,
        iterable|object|null $values = null,
    ): string|Partial;

    /**
     * Renders a template fragment within a variable scope distinct from the
     * calling View object.
     *
     * If no arguments are provided, returns object instance.
     *
     * @see PartialLoop
     *
     * @param string|null $name Name of view script
     * @param iterable|object $values Variables to populate in the view
     * @return ($name is string ? string : PartialLoop)
     * @throws Exception\InvalidArgumentException
     */
    public function partialLoop(string|null $name = null, iterable|object $values = []): PartialLoop|string;

    /** @see Placeholder */
    public function placeholder(string|null $placeholder = null): Placeholder;

    /**
     * Render the child model identified by $child
     *
     * If a matching child model is found, it is rendered. If not, an empty string is returned.
     *
     * @see RenderChildModel
     *
     * @param non-empty-string $child
     */
    public function renderChildModel(string $child): string;

    /** @see ViewModel */
    public function viewModel(): ViewModel;

    /** This method is here so that the custom template analyzer works */
    public function __internalPseudoRenderForPsalm(): void;
}
