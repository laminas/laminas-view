<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View;

use Interop\Container\ContainerInterface;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception;
use Zend\ServiceManager\Exception\InvalidServiceException;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\View\Exception\InvalidHelperException;

/**
 * Plugin manager implementation for view helpers
 *
 * Enforces that helpers retrieved are instances of
 * Helper\HelperInterface. Additionally, it registers a number of default
 * helpers.
 */
class HelperPluginManager extends AbstractPluginManager
{
    /**
     * Default helper aliases
     *
     * Most of these are present for legacy purposes, as v2 of the service
     * manager normalized names when fetching services.
     *
     * @var string[]
     */
    protected $aliases = [
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
    ];

    protected $instanceOf = Helper\HelperInterface::class;

    /**
     * Default factories
     *
     * basepath, doctype, and url are set up as factories in the ViewHelperManagerFactory.
     * basepath and url are not very useful without their factories, however the doctype
     * helper works fine as an invokable. The factory for doctype simply checks for the
     * config value from the merged config.
     *
     * @var array
     */
    protected $factories = [
        Helper\FlashMessenger::class      => Helper\Service\FlashMessengerFactory::class,
        'zendviewhelperflashmessenger'    => Helper\Service\FlashMessengerFactory::class,

        Helper\Identity::class            => Helper\Service\IdentityFactory::class,
        'zendviewhelperidentity'          => Helper\Service\IdentityFactory::class,

        Helper\BasePath::class            => InvokableFactory::class,
        'zendviewhelperbasepath'          => InvokableFactory::class,
        Helper\Cycle::class               => InvokableFactory::class,
        'zendviewhelpercycle'             => InvokableFactory::class,
        Helper\DeclareVars::class         => InvokableFactory::class,
        'zendviewhelperdeclarevars'       => InvokableFactory::class,
        Helper\Doctype::class             => InvokableFactory::class, // overridden by a factory in ViewHelperManagerFactory
        'zendviewhelperdoctype'           => InvokableFactory::class,
        Helper\EscapeHtml::class          => InvokableFactory::class,
        'zendviewhelperescapehtml'        => InvokableFactory::class,
        Helper\EscapeHtmlAttr::class      => InvokableFactory::class,
        'zendviewhelperescapehtmlattr'    => InvokableFactory::class,
        Helper\EscapeJs::class            => InvokableFactory::class,
        'zendviewhelperescapejs'          => InvokableFactory::class,
        Helper\EscapeCss::class           => InvokableFactory::class,
        'zendviewhelperescapecss'         => InvokableFactory::class,
        Helper\EscapeUrl::class           => InvokableFactory::class,
        'zendviewhelperescapeurl'         => InvokableFactory::class,
        Helper\Gravatar::class            => InvokableFactory::class,
        'zendviewhelpergravatar'          => InvokableFactory::class,
        Helper\HtmlTag::class             => InvokableFactory::class,
        'zendviewhelperhtmltag'           => InvokableFactory::class,
        Helper\HeadLink::class            => InvokableFactory::class,
        'zendviewhelperheadlink'          => InvokableFactory::class,
        Helper\HeadMeta::class            => InvokableFactory::class,
        'zendviewhelperheadmeta'          => InvokableFactory::class,
        Helper\HeadScript::class          => InvokableFactory::class,
        'zendviewhelperheadscript'        => InvokableFactory::class,
        Helper\HeadStyle::class           => InvokableFactory::class,
        'zendviewhelperheadstyle'         => InvokableFactory::class,
        Helper\HeadTitle::class           => InvokableFactory::class,
        'zendviewhelperheadtitle'         => InvokableFactory::class,
        Helper\HtmlFlash::class           => InvokableFactory::class,
        'zendviewhelperhtmlflash'         => InvokableFactory::class,
        Helper\HtmlList::class            => InvokableFactory::class,
        'zendviewhelperhtmllist'          => InvokableFactory::class,
        Helper\HtmlObject::class          => InvokableFactory::class,
        'zendviewhelperhtmlobject'        => InvokableFactory::class,
        Helper\HtmlPage::class            => InvokableFactory::class,
        'zendviewhelperhtmlpage'          => InvokableFactory::class,
        Helper\HtmlQuicktime::class       => InvokableFactory::class,
        'zendviewhelperhtmlquicktime'     => InvokableFactory::class,
        Helper\InlineScript::class        => InvokableFactory::class,
        'zendviewhelperinlinescript'      => InvokableFactory::class,
        Helper\Json::class                => InvokableFactory::class,
        'zendviewhelperjson'              => InvokableFactory::class,
        Helper\Layout::class              => InvokableFactory::class,
        'zendviewhelperlayout'            => InvokableFactory::class,
        Helper\PaginationControl::class   => InvokableFactory::class,
        'zendviewhelperpaginationcontrol' => InvokableFactory::class,
        Helper\PartialLoop::class         => InvokableFactory::class,
        'zendviewhelperpartialloop'       => InvokableFactory::class,
        Helper\Partial::class             => InvokableFactory::class,
        'zendviewhelperpartial'           => InvokableFactory::class,
        Helper\Placeholder::class         => InvokableFactory::class,
        'zendviewhelperplaceholder'       => InvokableFactory::class,
        Helper\RenderChildModel::class    => InvokableFactory::class,
        'zendviewhelperrenderchildmodel'  => InvokableFactory::class,
        Helper\RenderToPlaceholder::class => InvokableFactory::class,
        'zendviewhelperrendertoplaceholder' => InvokableFactory::class,
        Helper\ServerUrl::class           => InvokableFactory::class,
        'zendviewhelperserverurl'         => InvokableFactory::class,
        Helper\Url::class                 => InvokableFactory::class,
        'zendviewhelperurl'               => InvokableFactory::class,
        Helper\ViewModel::class           => InvokableFactory::class,
        'zendviewhelperviewmodel'         => InvokableFactory::class,
    ];

    /**
     * @var Renderer\RendererInterface
     */
    protected $renderer;

    /**
     * Constructor
     *
     * Merges provided configuration with default configuration.
     *
     * Adds initializers to inject the attached renderer and translator, if
     * any, to the currently requested helper.
     *
     * @param ContainerInterface $container
     * @param array $config
     */
    public function __construct(ContainerInterface $container, array $config = [])
    {
        $this->initializers[] = [$this, 'injectRenderer'];
        $this->initializers[] = [$this, 'injectTranslator'];

        parent::__construct($container, $config);
    }

    /**
     * Set renderer
     *
     * @param  Renderer\RendererInterface $renderer
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
     * @param $first
     * @param $second
     */
    public function injectRenderer($first, $second)
    {
        if ($first instanceof ContainerInterface) {
            $helper = $second;
        } else {
            $helper = $first;
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
     * @param $first
     * @param $second
     */
    public function injectTranslator($first, $second)
    {
        if ($first instanceof ContainerInterface) {
            $container = $first;
            $helper = $second;
        } else {
            $container = $second->getServiceLocator();
            $helper = $first;
        }
        if (! $helper instanceof TranslatorAwareInterface) {
            return;
        }

        if ($container->has('MvcTranslator')) {
            $helper->setTranslator($container->get('MvcTranslator'));
            return;
        }

        if ($container->has('Zend\I18n\Translator\TranslatorInterface')) {
            $helper->setTranslator($container->get('Zend\I18n\Translator\TranslatorInterface'));
            return;
        }

        if ($container->has('Translator')) {
            $helper->setTranslator($container->get('Translator'));
            return;
        }
    }

    /**
     * Validate the plugin is of the expected type (v3).
     *
     * Validates against `$instanceOf`.
     *
     * @param mixed $instance
     * @throws InvalidServiceException
     */
    public function validate($instance)
    {
        if (!$instance instanceof $this->instanceOf) {
            throw new InvalidServiceException(
                sprintf(
                    '%s can only create instances of %s; %s is invalid',
                    get_class($this),
                    $this->instanceOf,
                    (is_object($instance) ? get_class($instance) : gettype($instance))
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
     * @throws InvalidHelperException
     */
    public function validatePlugin($instance)
    {
        try {
            $this->validate($instance);
        } catch (InvalidServiceException $e) {
            throw new InvalidHelperException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
