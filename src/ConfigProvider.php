<?php

declare(strict_types=1);

namespace Laminas\View;

use Laminas\ServiceManager\ConfigInterface;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;

use function array_merge;

/**
 * @psalm-import-type FactoriesConfigurationType from ConfigInterface
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 * @psalm-type ViewHelperConfigurationType = array{
 *     view_helpers?: ServiceManagerConfiguration,
 *     view_helper_config?: array<string, array<array-key, mixed>
 * }
 * @psalm-suppress DeprecatedClass
 */
final class ConfigProvider
{
    /** @return ViewHelperConfigurationType */
    public function __invoke(): array
    {
        return [
            'view_helpers'       => self::viewHelperDependencyConfiguration(),
            'view_helper_config' => [],
        ];
    }

    /**
     * @psalm-suppress DeprecatedClass
     * @return ServiceManagerConfiguration
     */
    public static function viewHelperDependencyConfiguration(): array
    {
        return [
            'factories' => self::defaultViewHelperFactories(),
            'aliases'   => self::defaultViewHelperAliases(),
        ];
    }

    /**
     * Default factories
     *
     * basepath, doctype, and url are set up as factories in \Laminas\Mvc\ ViewHelperManagerFactory.
     * basepath and url are not very useful without their factories, however the doctype
     * helper works fine as an invokable. The factory for doctype simply checks for the
     * config value from the merged config.
     *
     * @return FactoriesConfigurationType
     */
    public static function defaultViewHelperFactories(): array
    {
        return [
            Helper\Asset::class               => Helper\Service\AssetFactory::class,
            Helper\HtmlAttributes::class      => InvokableFactory::class,
            Helper\FlashMessenger::class      => Helper\Service\FlashMessengerFactory::class,
            Helper\Identity::class            => Helper\Service\IdentityFactory::class,
            Helper\BasePath::class            => InvokableFactory::class,
            Helper\Cycle::class               => InvokableFactory::class,
            Helper\DeclareVars::class         => InvokableFactory::class,
            Helper\Doctype::class             => Helper\Service\DoctypeFactory::class,
            Helper\EscapeHtml::class          => InvokableFactory::class,
            Helper\EscapeHtmlAttr::class      => InvokableFactory::class,
            Helper\EscapeJs::class            => InvokableFactory::class,
            Helper\EscapeCss::class           => InvokableFactory::class,
            Helper\EscapeUrl::class           => InvokableFactory::class,
            Helper\Gravatar::class            => InvokableFactory::class,
            Helper\HtmlTag::class             => InvokableFactory::class,
            Helper\HeadLink::class            => InvokableFactory::class,
            Helper\HeadMeta::class            => InvokableFactory::class,
            Helper\HeadScript::class          => InvokableFactory::class,
            Helper\HeadStyle::class           => InvokableFactory::class,
            Helper\HeadTitle::class           => InvokableFactory::class,
            Helper\HtmlFlash::class           => InvokableFactory::class,
            Helper\HtmlList::class            => InvokableFactory::class,
            Helper\HtmlObject::class          => InvokableFactory::class,
            Helper\HtmlPage::class            => InvokableFactory::class,
            Helper\HtmlQuicktime::class       => InvokableFactory::class,
            Helper\InlineScript::class        => InvokableFactory::class,
            Helper\Json::class                => InvokableFactory::class,
            Helper\Layout::class              => InvokableFactory::class,
            Helper\PaginationControl::class   => InvokableFactory::class,
            Helper\PartialLoop::class         => InvokableFactory::class,
            Helper\Partial::class             => InvokableFactory::class,
            Helper\Placeholder::class         => InvokableFactory::class,
            Helper\RenderChildModel::class    => InvokableFactory::class,
            Helper\RenderToPlaceholder::class => InvokableFactory::class,
            Helper\ServerUrl::class           => InvokableFactory::class,
            Helper\Url::class                 => InvokableFactory::class,
            Helper\ViewModel::class           => InvokableFactory::class,
        ];
    }

    /**
     * Default helper aliases
     *
     * Most of these are present for legacy purposes, as v2 of the service
     * manager normalized names when fetching services.
     *
     * @return array<string,string>|array<array-key, string>
     */
    public static function defaultViewHelperAliases(): array
    {
        return array_merge(
            self::zendFrameworkHelperAliases(),
            self::legacyNormalizedHelperAliases(),
            self::standardHelperAliases()
        );
    }

    /**
     * These aliases provide compatibility with legacy Zend helpers
     *
     * @todo Remove this method in 3.0
     * @return array<string,string>
     */
    private static function zendFrameworkHelperAliases(): array
    {
        return [
            // @codingStandardsIgnoreStart
            'Zend\View\Helper\Asset'               => Helper\Asset::class,
            'Zend\View\Helper\FlashMessenger'      => Helper\FlashMessenger::class,
            'Zend\View\Helper\Identity'            => Helper\Identity::class,
            'Zend\View\Helper\BasePath'            => Helper\BasePath::class,
            'Zend\View\Helper\Cycle'               => Helper\Cycle::class,
            'Zend\View\Helper\DeclareVars'         => Helper\DeclareVars::class,
            'Zend\View\Helper\Doctype'             => Helper\Doctype::class,
            'Zend\View\Helper\EscapeHtml'          => Helper\EscapeHtml::class,
            'Zend\View\Helper\EscapeHtmlAttr'      => Helper\EscapeHtmlAttr::class,
            'Zend\View\Helper\EscapeJs'            => Helper\EscapeJs::class,
            'Zend\View\Helper\EscapeCss'           => Helper\EscapeCss::class,
            'Zend\View\Helper\EscapeUrl'           => Helper\EscapeUrl::class,
            'Zend\View\Helper\Gravatar'            => Helper\Gravatar::class,
            'Zend\View\Helper\HtmlTag'             => Helper\HtmlTag::class,
            'Zend\View\Helper\HeadLink'            => Helper\HeadLink::class,
            'Zend\View\Helper\HeadMeta'            => Helper\HeadMeta::class,
            'Zend\View\Helper\HeadScript'          => Helper\HeadScript::class,
            'Zend\View\Helper\HeadStyle'           => Helper\HeadStyle::class,
            'Zend\View\Helper\HeadTitle'           => Helper\HeadTitle::class,
            'Zend\View\Helper\HtmlFlash'           => Helper\HtmlFlash::class,
            'Zend\View\Helper\HtmlList'            => Helper\HtmlList::class,
            'Zend\View\Helper\HtmlObject'          => Helper\HtmlObject::class,
            'Zend\View\Helper\HtmlPage'            => Helper\HtmlPage::class,
            'Zend\View\Helper\HtmlQuicktime'       => Helper\HtmlQuicktime::class,
            'Zend\View\Helper\InlineScript'        => Helper\InlineScript::class,
            'Zend\View\Helper\Json'                => Helper\Json::class,
            'Zend\View\Helper\Layout'              => Helper\Layout::class,
            'Zend\View\Helper\PaginationControl'   => Helper\PaginationControl::class,
            'Zend\View\Helper\PartialLoop'         => Helper\PartialLoop::class,
            'Zend\View\Helper\Partial'             => Helper\Partial::class,
            'Zend\View\Helper\Placeholder'         => Helper\Placeholder::class,
            'Zend\View\Helper\RenderChildModel'    => Helper\RenderChildModel::class,
            'Zend\View\Helper\RenderToPlaceholder' => Helper\RenderToPlaceholder::class,
            'Zend\View\Helper\ServerUrl'           => Helper\ServerUrl::class,
            'Zend\View\Helper\Url'                 => Helper\Url::class,
            'Zend\View\Helper\ViewModel'           => Helper\ViewModel::class,
            // @codingStandardsIgnoreEnd

            // v2 normalized FQCNs
            'zendviewhelperasset'               => Helper\Asset::class,
            'zendviewhelperflashmessenger'      => Helper\FlashMessenger::class,
            'zendviewhelperidentity'            => Helper\Identity::class,
            'zendviewhelperbasepath'            => Helper\BasePath::class,
            'zendviewhelpercycle'               => Helper\Cycle::class,
            'zendviewhelperdeclarevars'         => Helper\DeclareVars::class,
            'zendviewhelperdoctype'             => Helper\Doctype::class,
            'zendviewhelperescapehtml'          => Helper\EscapeHtml::class,
            'zendviewhelperescapehtmlattr'      => Helper\EscapeHtmlAttr::class,
            'zendviewhelperescapejs'            => Helper\EscapeJs::class,
            'zendviewhelperescapecss'           => Helper\EscapeCss::class,
            'zendviewhelperescapeurl'           => Helper\EscapeUrl::class,
            'zendviewhelpergravatar'            => Helper\Gravatar::class,
            'zendviewhelperhtmltag'             => Helper\HtmlTag::class,
            'zendviewhelperheadlink'            => Helper\HeadLink::class,
            'zendviewhelperheadmeta'            => Helper\HeadMeta::class,
            'zendviewhelperheadscript'          => Helper\HeadScript::class,
            'zendviewhelperheadstyle'           => Helper\HeadStyle::class,
            'zendviewhelperheadtitle'           => Helper\HeadTitle::class,
            'zendviewhelperhtmlflash'           => Helper\HtmlFlash::class,
            'zendviewhelperhtmllist'            => Helper\HtmlList::class,
            'zendviewhelperhtmlobject'          => Helper\HtmlObject::class,
            'zendviewhelperhtmlpage'            => Helper\HtmlPage::class,
            'zendviewhelperhtmlquicktime'       => Helper\HtmlQuicktime::class,
            'zendviewhelperinlinescript'        => Helper\InlineScript::class,
            'zendviewhelperjson'                => Helper\Json::class,
            'zendviewhelperlayout'              => Helper\Layout::class,
            'zendviewhelperpaginationcontrol'   => Helper\PaginationControl::class,
            'zendviewhelperpartialloop'         => Helper\PartialLoop::class,
            'zendviewhelperpartial'             => Helper\Partial::class,
            'zendviewhelperplaceholder'         => Helper\Placeholder::class,
            'zendviewhelperrenderchildmodel'    => Helper\RenderChildModel::class,
            'zendviewhelperrendertoplaceholder' => Helper\RenderToPlaceholder::class,
            'zendviewhelperserverurl'           => Helper\ServerUrl::class,
            'zendviewhelperurl'                 => Helper\Url::class,
            'zendviewhelperviewmodel'           => Helper\ViewModel::class,
        ];
    }

    /**
     * These are the standard aliases used for the shipped view helpers
     *
     * @return array<string,string>
     */
    private static function standardHelperAliases(): array
    {
        return [
            'asset'               => Helper\Asset::class,
            'basePath'            => Helper\BasePath::class,
            'cycle'               => Helper\Cycle::class,
            'declareVars'         => Helper\DeclareVars::class,
            'doctype'             => Helper\Doctype::class, // overridden by a factory in ViewHelperManagerFactory
            'escapeCss'           => Helper\EscapeCss::class,
            'escapeHtmlAttr'      => Helper\EscapeHtmlAttr::class,
            'escapeHtml'          => Helper\EscapeHtml::class,
            'escapeJs'            => Helper\EscapeJs::class,
            'escapeUrl'           => Helper\EscapeUrl::class,
            'flashMessenger'      => Helper\FlashMessenger::class,
            'gravatar'            => Helper\Gravatar::class,
            'headLink'            => Helper\HeadLink::class,
            'headMeta'            => Helper\HeadMeta::class,
            'headScript'          => Helper\HeadScript::class,
            'headStyle'           => Helper\HeadStyle::class,
            'headTitle'           => Helper\HeadTitle::class,
            'htmlAttributes'      => Helper\HtmlAttributes::class,
            'htmlFlash'           => Helper\HtmlFlash::class,
            'htmlList'            => Helper\HtmlList::class,
            'htmlObject'          => Helper\HtmlObject::class,
            'htmlPage'            => Helper\HtmlPage::class,
            'htmlQuicktime'       => Helper\HtmlQuicktime::class,
            'htmlTag'             => Helper\HtmlTag::class,
            'identity'            => Helper\Identity::class,
            'inlineScript'        => Helper\InlineScript::class,
            'json'                => Helper\Json::class,
            'layout'              => Helper\Layout::class,
            'paginationControl'   => Helper\PaginationControl::class,
            'partial'             => Helper\Partial::class,
            'partialLoop'         => Helper\PartialLoop::class,
            'placeholder'         => Helper\Placeholder::class,
            'renderChildModel'    => Helper\RenderChildModel::class,
            'renderToPlaceholder' => Helper\RenderToPlaceholder::class,
            'serverUrl'           => Helper\ServerUrl::class,
            'url'                 => Helper\Url::class,
            'viewModel'           => Helper\ViewModel::class,
        ];
    }

    /**
     * Legacy normalized helper aliases that were typical from v2 Service Manager usage
     *
     * @todo Remove this method in 3.0
     * @return array<string,string>
     */
    private static function legacyNormalizedHelperAliases(): array
    {
        return [
            'Asset'               => Helper\Asset::class,
            'BasePath'            => Helper\BasePath::class,
            'basepath'            => Helper\BasePath::class,
            'Cycle'               => Helper\Cycle::class,
            'DeclareVars'         => Helper\DeclareVars::class,
            'declarevars'         => Helper\DeclareVars::class,
            'Doctype'             => Helper\Doctype::class,
            'EscapeCss'           => Helper\EscapeCss::class,
            'escapecss'           => Helper\EscapeCss::class,
            'EscapeHtmlAttr'      => Helper\EscapeHtmlAttr::class,
            'escapehtmlattr'      => Helper\EscapeHtmlAttr::class,
            'EscapeHtml'          => Helper\EscapeHtml::class,
            'escapehtml'          => Helper\EscapeHtml::class,
            'EscapeJs'            => Helper\EscapeJs::class,
            'escapejs'            => Helper\EscapeJs::class,
            'EscapeUrl'           => Helper\EscapeUrl::class,
            'escapeurl'           => Helper\EscapeUrl::class,
            'flashmessenger'      => Helper\FlashMessenger::class,
            'FlashMessenger'      => Helper\FlashMessenger::class,
            'Gravatar'            => Helper\Gravatar::class,
            'HeadLink'            => Helper\HeadLink::class,
            'headlink'            => Helper\HeadLink::class,
            'HeadMeta'            => Helper\HeadMeta::class,
            'headmeta'            => Helper\HeadMeta::class,
            'HeadScript'          => Helper\HeadScript::class,
            'headscript'          => Helper\HeadScript::class,
            'HeadStyle'           => Helper\HeadStyle::class,
            'headstyle'           => Helper\HeadStyle::class,
            'HeadTitle'           => Helper\HeadTitle::class,
            'headtitle'           => Helper\HeadTitle::class,
            'htmlattributes'      => Helper\HtmlAttributes::class,
            'HtmlAttributes'      => Helper\HtmlAttributes::class,
            'htmlflash'           => Helper\HtmlFlash::class,
            'HtmlFlash'           => Helper\HtmlFlash::class,
            'htmllist'            => Helper\HtmlList::class,
            'HtmlList'            => Helper\HtmlList::class,
            'htmlobject'          => Helper\HtmlObject::class,
            'HtmlObject'          => Helper\HtmlObject::class,
            'htmlpage'            => Helper\HtmlPage::class,
            'HtmlPage'            => Helper\HtmlPage::class,
            'htmlquicktime'       => Helper\HtmlQuicktime::class,
            'HtmlQuicktime'       => Helper\HtmlQuicktime::class,
            'htmltag'             => Helper\HtmlTag::class,
            'HtmlTag'             => Helper\HtmlTag::class,
            'Identity'            => Helper\Identity::class,
            'inlinescript'        => Helper\InlineScript::class,
            'InlineScript'        => Helper\InlineScript::class,
            'Json'                => Helper\Json::class,
            'Layout'              => Helper\Layout::class,
            'paginationcontrol'   => Helper\PaginationControl::class,
            'PaginationControl'   => Helper\PaginationControl::class,
            'partialloop'         => Helper\PartialLoop::class,
            'PartialLoop'         => Helper\PartialLoop::class,
            'Partial'             => Helper\Partial::class,
            'Placeholder'         => Helper\Placeholder::class,
            'renderchildmodel'    => Helper\RenderChildModel::class,
            'RenderChildModel'    => Helper\RenderChildModel::class,
            'render_child_model'  => Helper\RenderChildModel::class,
            'rendertoplaceholder' => Helper\RenderToPlaceholder::class,
            'RenderToPlaceholder' => Helper\RenderToPlaceholder::class,
            'serverurl'           => Helper\ServerUrl::class,
            'ServerUrl'           => Helper\ServerUrl::class,
            'Url'                 => Helper\Url::class,
            'view_model'          => Helper\ViewModel::class,
            'viewmodel'           => Helper\ViewModel::class,
            'ViewModel'           => Helper\ViewModel::class,
            // v2 canonical FQCNs
            'laminasviewhelperasset'               => Helper\Asset::class,
            'laminasviewhelperbasepath'            => Helper\BasePath::class,
            'laminasviewhelpercycle'               => Helper\Cycle::class,
            'laminasviewhelperdeclarevars'         => Helper\DeclareVars::class,
            'laminasviewhelperdoctype'             => Helper\Doctype::class,
            'laminasviewhelperescapecss'           => Helper\EscapeCss::class,
            'laminasviewhelperescapehtml'          => Helper\EscapeHtml::class,
            'laminasviewhelperescapehtmlattr'      => Helper\EscapeHtmlAttr::class,
            'laminasviewhelperescapejs'            => Helper\EscapeJs::class,
            'laminasviewhelperescapeurl'           => Helper\EscapeUrl::class,
            'laminasviewhelperflashmessenger'      => Helper\FlashMessenger::class,
            'laminasviewhelpergravatar'            => Helper\Gravatar::class,
            'laminasviewhelperheadlink'            => Helper\HeadLink::class,
            'laminasviewhelperheadmeta'            => Helper\HeadMeta::class,
            'laminasviewhelperheadscript'          => Helper\HeadScript::class,
            'laminasviewhelperheadstyle'           => Helper\HeadStyle::class,
            'laminasviewhelperheadtitle'           => Helper\HeadTitle::class,
            'laminasviewhelperhtmlflash'           => Helper\HtmlFlash::class,
            'laminasviewhelperhtmllist'            => Helper\HtmlList::class,
            'laminasviewhelperhtmlobject'          => Helper\HtmlObject::class,
            'laminasviewhelperhtmlpage'            => Helper\HtmlPage::class,
            'laminasviewhelperhtmlquicktime'       => Helper\HtmlQuicktime::class,
            'laminasviewhelperhtmltag'             => Helper\HtmlTag::class,
            'laminasviewhelperidentity'            => Helper\Identity::class,
            'laminasviewhelperinlinescript'        => Helper\InlineScript::class,
            'laminasviewhelperjson'                => Helper\Json::class,
            'laminasviewhelperlayout'              => Helper\Layout::class,
            'laminasviewhelperpaginationcontrol'   => Helper\PaginationControl::class,
            'laminasviewhelperpartialloop'         => Helper\PartialLoop::class,
            'laminasviewhelperpartial'             => Helper\Partial::class,
            'laminasviewhelperplaceholder'         => Helper\Placeholder::class,
            'laminasviewhelperrenderchildmodel'    => Helper\RenderChildModel::class,
            'laminasviewhelperrendertoplaceholder' => Helper\RenderToPlaceholder::class,
            'laminasviewhelperserverurl'           => Helper\ServerUrl::class,
            'laminasviewhelperurl'                 => Helper\Url::class,
            'laminasviewhelperviewmodel'           => Helper\ViewModel::class,
        ];
    }
}
