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
 * @psalm-suppress DeprecatedClass, DeprecatedMethod
 */
final class ConfigProvider
{
    /** @return ViewHelperConfigurationType */
    public function __invoke(): array
    {
        return [
            'view_helpers'       => $this->viewHelperDependencyConfiguration(),
            'view_helper_config' => [],
        ];
    }

    /**
     * @return ServiceManagerConfiguration
     */
    private function viewHelperDependencyConfiguration(): array
    {
        return [
            'factories' => $this->defaultViewHelperFactories(),
            'aliases'   => $this->defaultViewHelperAliases(),
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
    private function defaultViewHelperFactories(): array
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
     * These are the standard aliases used for the shipped view helpers
     *
     * @return array<string,string>
     */
    private function defaultViewHelperAliases(): array
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
}
