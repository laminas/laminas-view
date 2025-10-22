<?php

declare(strict_types=1);

namespace Laminas\View;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Helper\Service\EscapeHelperFactory;
use Laminas\View\Helper\Service\GenericFactory;
use Laminas\View\Helper\StatefulHelperInterface;
use Psr\Container\ContainerInterface;

use function array_replace_recursive;
use function get_debug_type;
use function is_callable;
use function spl_object_id;
use function sprintf;

/**
 * Plugin manager implementation for view helpers
 *
 * Enforces that helpers retrieved are callable.
 * Additionally, it registers a number of default helpers and tracks stateful helpers so that state can be reset.
 *
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 * @extends AbstractPluginManager<callable>
 */
final class HelperPluginManager extends AbstractPluginManager implements HelperPluginManagerInterface
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
            Helper\HeadLink::class            => GenericFactory::class,
            Helper\HeadMeta::class            => GenericFactory::class,
            Helper\HeadScript::class          => GenericFactory::class,
            Helper\HeadStyle::class           => GenericFactory::class,
            Helper\HeadTitle::class           => Helper\Service\HeadTitleFactory::class,
            Helper\HtmlAttributes::class      => EscapeHelperFactory::class,
            Helper\HtmlList::class            => EscapeHelperFactory::class,
            Helper\HtmlObject::class          => GenericFactory::class,
            Helper\HtmlTag::class             => GenericFactory::class,
            Helper\InlineScript::class        => GenericFactory::class,
            Helper\Layout::class              => Helper\Service\LayoutFactory::class,
            Helper\PartialLoop::class         => Helper\Service\PartialLoopFactory::class,
            Helper\Partial::class             => Helper\Service\PartialFactory::class,
            Helper\Placeholder::class         => InvokableFactory::class,
            Helper\RenderToPlaceholder::class => Helper\Service\RenderToPlaceholderFactory::class,
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
            'rendertoplaceholder' => Helper\RenderToPlaceholder::class,
            'renderToPlaceholder' => Helper\RenderToPlaceholder::class,
            'RenderToPlaceholder' => Helper\RenderToPlaceholder::class,
            'view_model'          => Helper\ViewModel::class,
            'viewmodel'           => Helper\ViewModel::class,
            'viewModel'           => Helper\ViewModel::class,
            'ViewModel'           => Helper\ViewModel::class,
        ],
    ];

    /**
     * A hash map of `spl_object_id` to Helper instance
     *
     * @var array<int, StatefulHelperInterface>
     */
    private array $statefulHelpers = [];

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
        /** @psalm-var ServiceManagerConfiguration $config Psalm cannot infer this after merge */
        $config = array_replace_recursive(self::CONFIG, $config);

        parent::__construct($creationContext, $config);
    }

    /**
     * Validate the plugin is of the expected type.
     *
     * Validates against callables and HelperInterface implementations.
     *
     * @throws InvalidServiceException
     * @psalm-assert callable $instance
     */
    public function validate(mixed $instance): void
    {
        if (! is_callable($instance)) {
            throw new InvalidServiceException(
                sprintf(
                    '%s can only create callables; %s is invalid',
                    self::class,
                    get_debug_type($instance),
                ),
            );
        }
    }

    /**
     * @template InstanceParam
     * @param class-string<InstanceParam>|string $id Service name of plugin to retrieve.
     * @return ($id is class-string<InstanceParam> ? InstanceParam : callable)
     */
    public function get(string $id): callable
    {
        /** @psalm-var callable $plugin Unfortunately this type needs forcing */
        $plugin = parent::get($id);
        if ($plugin instanceof StatefulHelperInterface) {
            $this->statefulHelpers[spl_object_id($plugin)] = $plugin;
        }

        return $plugin;
    }

    public function resetState(): void
    {
        foreach ($this->statefulHelpers as $helper) {
            $helper->resetState();
        }

        $this->statefulHelpers = [];
    }
}
