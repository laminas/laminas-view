<?php

declare(strict_types=1);

namespace Laminas\View;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\View\Helper\HelperInterface;
use Laminas\View\Helper\Service\EscapeHelperFactory;
use Psr\Container\ContainerInterface;

use function array_replace_recursive;
use function get_debug_type;
use function is_callable;
use function sprintf;

/**
 * Plugin manager implementation for view helpers
 *
 * Enforces that helpers retrieved are instances of
 * Helper\HelperInterface. Additionally, it registers a number of default
 * helpers.
 *
 * @extends AbstractPluginManager<HelperInterface|callable>
 */
final class HelperPluginManager extends AbstractPluginManager
{
    private const CONFIG = [
        'factories' => [
            Helper\Asset::class               => Helper\Service\AssetFactory::class,
            Helper\BasePath::class            => Helper\Service\BasePathFactory::class,
            Helper\Cycle::class               => InvokableFactory::class,
            Helper\Doctype::class             => Helper\Service\DoctypeFactory::class,
            Helper\EscapeCss::class           => EscapeHelperFactory::class,
            Helper\EscapeHtml::class          => EscapeHelperFactory::class,
            Helper\EscapeHtmlAttr::class      => EscapeHelperFactory::class,
            Helper\EscapeJs::class            => EscapeHelperFactory::class,
            Helper\EscapeUrl::class           => EscapeHelperFactory::class,
            Helper\GravatarImage::class       => EscapeHelperFactory::class,
            Helper\HeadLink::class            => Helper\Service\HeadLinkFactory::class,
            Helper\HeadMeta::class            => Helper\Service\HeadMetaFactory::class,
            Helper\HeadScript::class          => InvokableFactory::class,
            Helper\HeadStyle::class           => InvokableFactory::class,
            Helper\HeadTitle::class           => Helper\Service\HeadTitleFactory::class,
            Helper\HtmlAttributes::class      => EscapeHelperFactory::class,
            Helper\HtmlList::class            => EscapeHelperFactory::class,
            Helper\HtmlObject::class          => Helper\Service\HtmlObjectFactory::class,
            Helper\HtmlTag::class             => InvokableFactory::class,
            Helper\Identity::class            => Helper\Service\IdentityFactory::class,
            Helper\InlineScript::class        => InvokableFactory::class,
            Helper\Layout::class              => Helper\Service\LayoutFactory::class,
            Helper\PartialLoop::class         => Helper\Service\PartialLoopFactory::class,
            Helper\Partial::class             => Helper\Service\PartialFactory::class,
            Helper\Placeholder::class         => InvokableFactory::class,
            Helper\RenderChildModel::class    => Helper\Service\RenderChildModelFactory::class,
            Helper\RenderToPlaceholder::class => Helper\Service\RenderToPlaceholderFactory::class,
            Helper\ServerUrl::class           => InvokableFactory::class,
            Helper\ViewModel::class           => InvokableFactory::class,
        ],
        'aliases'   => [
            'asset'               => Helper\Asset::class,
            'Asset'               => Helper\Asset::class,
            'basePath'            => Helper\BasePath::class,
            'BasePath'            => Helper\BasePath::class,
            'basepath'            => Helper\BasePath::class,
            'Cycle'               => Helper\Cycle::class,
            'cycle'               => Helper\Cycle::class,
            'Doctype'             => Helper\Doctype::class,
            'doctype'             => Helper\Doctype::class, // overridden by a factory in ViewHelperManagerFactory
            'escapeCss'           => Helper\EscapeCss::class,
            'EscapeCss'           => Helper\EscapeCss::class,
            'escapecss'           => Helper\EscapeCss::class,
            'escapeHtmlAttr'      => Helper\EscapeHtmlAttr::class,
            'EscapeHtmlAttr'      => Helper\EscapeHtmlAttr::class,
            'escapehtmlattr'      => Helper\EscapeHtmlAttr::class,
            'escapeHtml'          => Helper\EscapeHtml::class,
            'EscapeHtml'          => Helper\EscapeHtml::class,
            'escapehtml'          => Helper\EscapeHtml::class,
            'escapeJs'            => Helper\EscapeJs::class,
            'EscapeJs'            => Helper\EscapeJs::class,
            'escapejs'            => Helper\EscapeJs::class,
            'escapeUrl'           => Helper\EscapeUrl::class,
            'EscapeUrl'           => Helper\EscapeUrl::class,
            'escapeurl'           => Helper\EscapeUrl::class,
            'gravatarImage'       => Helper\GravatarImage::class,
            'headLink'            => Helper\HeadLink::class,
            'HeadLink'            => Helper\HeadLink::class,
            'headlink'            => Helper\HeadLink::class,
            'headMeta'            => Helper\HeadMeta::class,
            'HeadMeta'            => Helper\HeadMeta::class,
            'headmeta'            => Helper\HeadMeta::class,
            'headScript'          => Helper\HeadScript::class,
            'HeadScript'          => Helper\HeadScript::class,
            'headscript'          => Helper\HeadScript::class,
            'headStyle'           => Helper\HeadStyle::class,
            'HeadStyle'           => Helper\HeadStyle::class,
            'headstyle'           => Helper\HeadStyle::class,
            'headTitle'           => Helper\HeadTitle::class,
            'HeadTitle'           => Helper\HeadTitle::class,
            'headtitle'           => Helper\HeadTitle::class,
            'htmlattributes'      => Helper\HtmlAttributes::class,
            'htmlAttributes'      => Helper\HtmlAttributes::class,
            'HtmlAttributes'      => Helper\HtmlAttributes::class,
            'htmllist'            => Helper\HtmlList::class,
            'htmlList'            => Helper\HtmlList::class,
            'HtmlList'            => Helper\HtmlList::class,
            'htmlobject'          => Helper\HtmlObject::class,
            'htmlObject'          => Helper\HtmlObject::class,
            'HtmlObject'          => Helper\HtmlObject::class,
            'htmltag'             => Helper\HtmlTag::class,
            'htmlTag'             => Helper\HtmlTag::class,
            'HtmlTag'             => Helper\HtmlTag::class,
            'identity'            => Helper\Identity::class,
            'Identity'            => Helper\Identity::class,
            'inlinescript'        => Helper\InlineScript::class,
            'inlineScript'        => Helper\InlineScript::class,
            'InlineScript'        => Helper\InlineScript::class,
            'layout'              => Helper\Layout::class,
            'Layout'              => Helper\Layout::class,
            'partial'             => Helper\Partial::class,
            'partialloop'         => Helper\PartialLoop::class,
            'partialLoop'         => Helper\PartialLoop::class,
            'PartialLoop'         => Helper\PartialLoop::class,
            'Partial'             => Helper\Partial::class,
            'placeholder'         => Helper\Placeholder::class,
            'Placeholder'         => Helper\Placeholder::class,
            'renderchildmodel'    => Helper\RenderChildModel::class,
            'renderChildModel'    => Helper\RenderChildModel::class,
            'RenderChildModel'    => Helper\RenderChildModel::class,
            'render_child_model'  => Helper\RenderChildModel::class,
            'rendertoplaceholder' => Helper\RenderToPlaceholder::class,
            'renderToPlaceholder' => Helper\RenderToPlaceholder::class,
            'RenderToPlaceholder' => Helper\RenderToPlaceholder::class,
            'serverurl'           => Helper\ServerUrl::class,
            'serverUrl'           => Helper\ServerUrl::class,
            'ServerUrl'           => Helper\ServerUrl::class,
            'view_model'          => Helper\ViewModel::class,
            'viewmodel'           => Helper\ViewModel::class,
            'viewModel'           => Helper\ViewModel::class,
            'ViewModel'           => Helper\ViewModel::class,
        ],
    ];

    /**
     * Constructor
     *
     * Merges provided configuration with default configuration.
     *
     * @inheritDoc
     */
    public function __construct(
        ContainerInterface $creationContext,
        array $config = [],
    ) {
        $config = array_replace_recursive(self::CONFIG, $config);

        parent::__construct($creationContext, $config);
    }

    /**
     * Validate the plugin is of the expected type.
     *
     * Validates against callables and HelperInterface implementations.
     *
     * @throws InvalidServiceException
     * @psalm-assert HelperInterface|callable $instance
     */
    public function validate(mixed $instance): void
    {
        if (! is_callable($instance) && ! $instance instanceof HelperInterface) {
            throw new InvalidServiceException(
                sprintf(
                    '%s can only create instances of %s and/or callables; %s is invalid',
                    self::class,
                    HelperInterface::class,
                    get_debug_type($instance),
                ),
            );
        }
    }
}
