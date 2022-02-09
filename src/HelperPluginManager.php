<?php

declare(strict_types=1);

namespace Laminas\View;

use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\I18n\Translator\TranslatorAwareInterface;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\ConfigInterface;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Exception\InvalidHelperException;
use Psr\Container\ContainerInterface;

use function get_class;
use function gettype;
use function is_callable;
use function is_object;
use function method_exists;
use function sprintf;

/**
 * Plugin manager implementation for view helpers
 *
 * Enforces that helpers retrieved are instances of
 * Helper\HelperInterface. Additionally, it registers a number of default
 * helpers.
 *
 * @final
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 * @psalm-import-type FactoriesConfigurationType from ConfigInterface
 */
class HelperPluginManager extends AbstractPluginManager
{
    /**
     * Default helper aliases
     *
     * Most of these are present for legacy purposes, as v2 of the service
     * manager normalized names when fetching services.
     *
     * @psalm-suppress DeprecatedClass
     * @var array<string, string>|array<array-key, string>
     */
    protected $aliases = [
        'asset'               => Helper\Asset::class,
        'Asset'               => Helper\Asset::class,
        'basePath'            => Helper\BasePath::class,
        'BasePath'            => Helper\BasePath::class,
        'basepath'            => Helper\BasePath::class,
        'Cycle'               => Helper\Cycle::class,
        'cycle'               => Helper\Cycle::class,
        'declareVars'         => Helper\DeclareVars::class,
        'DeclareVars'         => Helper\DeclareVars::class,
        'declarevars'         => Helper\DeclareVars::class,
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
        'flashmessenger'      => Helper\FlashMessenger::class,
        'flashMessenger'      => Helper\FlashMessenger::class,
        'FlashMessenger'      => Helper\FlashMessenger::class,
        'Gravatar'            => Helper\Gravatar::class,
        'gravatar'            => Helper\Gravatar::class,
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
        'htmlflash'           => Helper\HtmlFlash::class,
        'htmlFlash'           => Helper\HtmlFlash::class,
        'HtmlFlash'           => Helper\HtmlFlash::class,
        'htmllist'            => Helper\HtmlList::class,
        'htmlList'            => Helper\HtmlList::class,
        'HtmlList'            => Helper\HtmlList::class,
        'htmlobject'          => Helper\HtmlObject::class,
        'htmlObject'          => Helper\HtmlObject::class,
        'HtmlObject'          => Helper\HtmlObject::class,
        'htmlpage'            => Helper\HtmlPage::class,
        'htmlPage'            => Helper\HtmlPage::class,
        'HtmlPage'            => Helper\HtmlPage::class,
        'htmlquicktime'       => Helper\HtmlQuicktime::class,
        'htmlQuicktime'       => Helper\HtmlQuicktime::class,
        'HtmlQuicktime'       => Helper\HtmlQuicktime::class,
        'htmltag'             => Helper\HtmlTag::class,
        'htmlTag'             => Helper\HtmlTag::class,
        'HtmlTag'             => Helper\HtmlTag::class,
        'identity'            => Helper\Identity::class,
        'Identity'            => Helper\Identity::class,
        'inlinescript'        => Helper\InlineScript::class,
        'inlineScript'        => Helper\InlineScript::class,
        'InlineScript'        => Helper\InlineScript::class,
        'json'                => Helper\Json::class,
        'Json'                => Helper\Json::class,
        'layout'              => Helper\Layout::class,
        'Layout'              => Helper\Layout::class,
        'paginationcontrol'   => Helper\PaginationControl::class,
        'paginationControl'   => Helper\PaginationControl::class,
        'PaginationControl'   => Helper\PaginationControl::class,
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
        'url'                 => Helper\Url::class,
        'Url'                 => Helper\Url::class,
        'view_model'          => Helper\ViewModel::class,
        'viewmodel'           => Helper\ViewModel::class,
        'viewModel'           => Helper\ViewModel::class,
        'ViewModel'           => Helper\ViewModel::class,

        /**
         * Legacy Zend Framework aliases
         *
         * @psalm-suppress DeprecatedClass
         * @codingStandardsIgnoreStart
         **/
        \Zend\View\Helper\Asset::class => Helper\Asset::class,
        \Zend\View\Helper\FlashMessenger::class => Helper\FlashMessenger::class,
        \Zend\View\Helper\Identity::class => Helper\Identity::class,
        \Zend\View\Helper\BasePath::class => Helper\BasePath::class,
        \Zend\View\Helper\Cycle::class => Helper\Cycle::class,
        \Zend\View\Helper\DeclareVars::class => Helper\DeclareVars::class,
        \Zend\View\Helper\Doctype::class => Helper\Doctype::class,
        \Zend\View\Helper\EscapeHtml::class => Helper\EscapeHtml::class,
        \Zend\View\Helper\EscapeHtmlAttr::class => Helper\EscapeHtmlAttr::class,
        \Zend\View\Helper\EscapeJs::class => Helper\EscapeJs::class,
        \Zend\View\Helper\EscapeCss::class => Helper\EscapeCss::class,
        \Zend\View\Helper\EscapeUrl::class => Helper\EscapeUrl::class,
        \Zend\View\Helper\Gravatar::class => Helper\Gravatar::class,
        \Zend\View\Helper\HtmlTag::class => Helper\HtmlTag::class,
        \Zend\View\Helper\HeadLink::class => Helper\HeadLink::class,
        \Zend\View\Helper\HeadMeta::class => Helper\HeadMeta::class,
        \Zend\View\Helper\HeadScript::class => Helper\HeadScript::class,
        \Zend\View\Helper\HeadStyle::class => Helper\HeadStyle::class,
        \Zend\View\Helper\HeadTitle::class => Helper\HeadTitle::class,
        \Zend\View\Helper\HtmlFlash::class => Helper\HtmlFlash::class,
        \Zend\View\Helper\HtmlList::class => Helper\HtmlList::class,
        \Zend\View\Helper\HtmlObject::class => Helper\HtmlObject::class,
        \Zend\View\Helper\HtmlPage::class => Helper\HtmlPage::class,
        \Zend\View\Helper\HtmlQuicktime::class => Helper\HtmlQuicktime::class,
        \Zend\View\Helper\InlineScript::class => Helper\InlineScript::class,
        \Zend\View\Helper\Json::class => Helper\Json::class,
        \Zend\View\Helper\Layout::class => Helper\Layout::class,
        \Zend\View\Helper\PaginationControl::class => Helper\PaginationControl::class,
        \Zend\View\Helper\PartialLoop::class => Helper\PartialLoop::class,
        \Zend\View\Helper\Partial::class => Helper\Partial::class,
        \Zend\View\Helper\Placeholder::class => Helper\Placeholder::class,
        \Zend\View\Helper\RenderChildModel::class => Helper\RenderChildModel::class,
        \Zend\View\Helper\RenderToPlaceholder::class => Helper\RenderToPlaceholder::class,
        \Zend\View\Helper\ServerUrl::class => Helper\ServerUrl::class,
        \Zend\View\Helper\Url::class => Helper\Url::class,
        \Zend\View\Helper\ViewModel::class => Helper\ViewModel::class,
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

    /**
     * Default factories
     *
     * basepath, doctype, and url are set up as factories in the ViewHelperManagerFactory.
     * basepath and url are not very useful without their factories, however the doctype
     * helper works fine as an invokable. The factory for doctype simply checks for the
     * config value from the merged config.
     *
     * @psalm-suppress DeprecatedClass
     * @var FactoriesConfigurationType
     */
    protected $factories = [
        Helper\Asset::class          => Helper\Service\AssetFactory::class,
        Helper\HtmlAttributes::class => InvokableFactory::class,
        Helper\FlashMessenger::class => Helper\Service\FlashMessengerFactory::class,
        Helper\Identity::class       => Helper\Service\IdentityFactory::class,
        Helper\BasePath::class       => InvokableFactory::class,
        Helper\Cycle::class          => InvokableFactory::class,
        Helper\DeclareVars::class    => InvokableFactory::class,
        // overridden in ViewHelperManagerFactory
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

    /** @var Renderer\RendererInterface|null */
    protected $renderer;

    /**
     * Constructor
     *
     * Merges provided configuration with default configuration.
     *
     * Adds initializers to inject the attached renderer and translator, if
     * any, to the currently requested helper.
     *
     * @param null|ConfigInterface|ContainerInterface $configOrContainerInstance
     * @param array $v3config If $configOrContainerInstance is a container, this
     *     value will be passed to the parent constructor.
     * @psalm-param ServiceManagerConfiguration $v3config
     */
    public function __construct($configOrContainerInstance = null, array $v3config = [])
    {
        $this->initializers[] = [$this, 'injectRenderer'];
        $this->initializers[] = [$this, 'injectTranslator'];
        $this->initializers[] = [$this, 'injectEventManager'];

        parent::__construct($configOrContainerInstance, $v3config);
    }

    /**
     * Set renderer
     *
     * @return HelperPluginManager
     */
    public function setRenderer(Renderer\RendererInterface $renderer)
    {
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * Retrieve renderer instance
     *
     * @return null|Renderer\RendererInterface
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * Inject a helper instance with the registered renderer
     *
     * @param ContainerInterface|Helper\HelperInterface $first helper instance
     *     under laminas-servicemanager v2, ContainerInterface under v3.
     * @param ContainerInterface|Helper\HelperInterface $second
     *     ContainerInterface under laminas-servicemanager v3, helper instance
     *     under v2. Ignored regardless.
     * @return void
     */
    public function injectRenderer($first, $second)
    {
        $helper = $first instanceof ContainerInterface
            ? $second
            : $first;

        if (! $helper instanceof Helper\HelperInterface) {
            return;
        }

        $renderer = $this->getRenderer();
        if (null === $renderer) {
            return;
        }
        $helper->setView($renderer);
    }

    /**
     * Inject a helper instance with the registered translator
     *
     * @param ContainerInterface|Helper\HelperInterface $first helper instance
     *     under laminas-servicemanager v2, ContainerInterface under v3.
     * @param ContainerInterface|Helper\HelperInterface $second
     *     ContainerInterface under laminas-servicemanager v3, helper instance
     *     under v2. Ignored regardless.
     * @return void
     */
    public function injectTranslator($first, $second)
    {
        if ($first instanceof ContainerInterface) {
            // v3 usage
            $container = $first;
            $helper    = $second;
        } else {
            // v2 usage; grab the parent container
            $container = $second->getServiceLocator();
            $helper    = $first;
        }

        // Allow either direct implementation or duck-typing.
        if (
            ! $helper instanceof TranslatorAwareInterface
            && ! method_exists($helper, 'setTranslator')
        ) {
            return;
        }

        if (! $container) {
            // Under laminas-navigation v2.5, the navigation PluginManager is
            // always lazy-loaded, which means it never has a parent
            // container.
            return;
        }

        if (method_exists($helper, 'hasTranslator') && $helper->hasTranslator()) {
            return;
        }

        if ($container->has('MvcTranslator')) {
            $helper->setTranslator($container->get('MvcTranslator'));
            return;
        }

        if ($container->has(TranslatorInterface::class)) {
            $helper->setTranslator($container->get(TranslatorInterface::class));
            return;
        }

        if ($container->has('Translator')) {
            $helper->setTranslator($container->get('Translator'));
            return;
        }
    }

    /**
     * Inject a helper instance with the registered event manager
     *
     * @param ContainerInterface|Helper\HelperInterface $first helper instance
     *     under laminas-servicemanager v2, ContainerInterface under v3.
     * @param ContainerInterface|Helper\HelperInterface $second
     *     ContainerInterface under laminas-servicemanager v3, helper instance
     *     under v2. Ignored regardless.
     * @return void
     */
    public function injectEventManager($first, $second)
    {
        if ($first instanceof ContainerInterface) {
            // v3 usage
            $container = $first;
            $helper    = $second;
        } else {
            // v2 usage; grab the parent container
            $container = $second->getServiceLocator();
            $helper    = $first;
        }

        if (! $container) {
            // Under laminas-navigation v2.5, the navigation PluginManager is
            // always lazy-loaded, which means it never has a parent
            // container.
            return;
        }

        if (! $helper instanceof EventManagerAwareInterface) {
            return;
        }

        if (! $container->has('EventManager')) {
            // If the container doesn't have an EM service, do nothing.
            return;
        }

        $events = $helper->getEventManager();
        if (! $events || ! $events->getSharedManager() instanceof SharedEventManagerInterface) {
            $helper->setEventManager($container->get('EventManager'));
        }
    }

    /**
     * Validate the plugin is of the expected type (v3).
     *
     * Validates against callables and HelperInterface implementations.
     *
     * @param mixed $instance
     * @throws InvalidServiceException
     */
    public function validate($instance)
    {
        if (! is_callable($instance) && ! $instance instanceof Helper\HelperInterface) {
            throw new InvalidServiceException(
                sprintf(
                    '%s can only create instances of %s and/or callables; %s is invalid',
                    static::class,
                    Helper\HelperInterface::class,
                    is_object($instance) ? get_class($instance) : gettype($instance)
                )
            );
        }
    }

    /**
     * Validate the plugin is of the expected type (v2).
     *
     * Proxies to `validate()`.
     *
     * @param mixed $instance
     * @return void
     * @throws InvalidHelperException
     */
    public function validatePlugin($instance)
    {
        try {
            $this->validate($instance);
        } catch (InvalidServiceException $e) {
            throw new InvalidHelperException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}
