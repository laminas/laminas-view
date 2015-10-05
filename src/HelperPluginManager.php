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
use Zend\ServiceManager\Factory\InvokableFactory;

/**
 * Plugin manager implementation for view helpers
 *
 * Enforces that helpers retrieved are instances of
 * Helper\HelperInterface. Additionally, it registers a number of default
 * helpers.
 */
class HelperPluginManager extends AbstractPluginManager
{
    protected $instanceOf = Helper\HelperInterface::class;

    /**
     * Default helper aliases
     *
     * Most of these are present for legacy purposes, as v2 of the service
     * manager normalized names when fetching services.
     *
     * @var string[]
     */
    protected $aliases = [
        'basePath'            => 'basepath',
        'BasePath'            => 'basepath',
        'basepath'            => Helper\BasePath::class,
        'Cycle'               => 'cycle',
        'cycle'               => Helper\Cycle::class,
        'declareVars'         => 'declarevars',
        'DeclareVars'         => 'declarevars',
        'declarevars'         => Helper\DeclareVars::class,
        'Doctype'             => 'doctype',
        'doctype'             => Helper\Doctype::class, // overridden by a factory in ViewHelperManagerFactory
        'escapeCss'           => 'escapecss',
        'EscapeCss'           => 'escapecss',
        'escapecss'           => Helper\EscapeCss::class,
        'escapeHtmlAttr'      => 'escapehtmlattr',
        'EscapeHtmlAttr'      => 'escapehtmlattr',
        'escapehtmlattr'      => Helper\EscapeHtmlAttr::class,
        'escapeHtml'          => 'escapehtml',
        'EscapeHtml'          => 'escapehtml',
        'escapehtml'          => Helper\EscapeHtml::class,
        'escapeJs'            => 'escapejs',
        'EscapeJs'            => 'escapejs',
        'escapejs'            => Helper\EscapeJs::class,
        'escapeUrl'           => 'escapeurl',
        'EscapeUrl'           => 'escapeurl',
        'escapeurl'           => Helper\EscapeUrl::class,
        'flashMessenger'      => 'flashmessenger',
        'FlashMessenger'      => 'flashmessenger',
        'Gravatar'            => 'gravatar',
        'gravatar'            => Helper\Gravatar::class,
        'headLink'            => 'headlink',
        'HeadLink'            => 'headlink',
        'headlink'            => Helper\HeadLink::class,
        'headMeta'            => 'headmeta',
        'HeadMeta'            => 'headmeta',
        'headmeta'            => Helper\HeadMeta::class,
        'headScript'          => 'headscript',
        'HeadScript'          => 'headscript',
        'headscript'          => Helper\HeadScript::class,
        'headStyle'           => 'headstyle',
        'HeadStyle'           => 'headstyle',
        'headstyle'           => Helper\HeadStyle::class,
        'headTitle'           => 'headtitle',
        'HeadTitle'           => 'headtitle',
        'headtitle'           => Helper\HeadTitle::class,
        'htmlflash'           => Helper\HtmlFlash::class,
        'htmlFlash'           => 'htmlflash',
        'HtmlFlash'           => 'htmlflash',
        'htmllist'            => Helper\HtmlList::class,
        'htmlList'            => 'htmllist',
        'HtmlList'            => 'htmllist',
        'htmlobject'          => Helper\HtmlObject::class,
        'htmlObject'          => 'htmlobject',
        'HtmlObject'          => 'htmlobject',
        'htmlpage'            => Helper\HtmlPage::class,
        'htmlPage'            => 'htmlpage',
        'HtmlPage'            => 'htmlpage',
        'htmlquicktime'       => Helper\HtmlQuicktime::class,
        'htmlQuicktime'       => 'htmlquicktime',
        'HtmlQuicktime'       => 'htmlquicktime',
        'htmltag'             => Helper\HtmlTag::class,
        'htmlTag'             => 'htmltag',
        'HtmlTag'             => 'htmltag',
        'Identity'            => 'identity',
        'inlinescript'        => Helper\InlineScript::class,
        'inlineScript'        => 'inlinescript',
        'InlineScript'        => 'inlinescript',
        'json'                => Helper\Json::class,
        'Json'                => 'json',
        'layout'              => Helper\Layout::class,
        'Layout'              => 'layout',
        'paginationcontrol'   => Helper\PaginationControl::class,
        'paginationControl'   => 'paginationcontrol',
        'PaginationControl'   => 'paginationcontrol',
        'partial'             => Helper\Partial::class,
        'partialloop'         => Helper\PartialLoop::class,
        'partialLoop'         => 'partialloop',
        'PartialLoop'         => 'partialloop',
        'Partial'             => 'partial',
        'placeholder'         => Helper\Placeholder::class,
        'Placeholder'         => 'placeholder',
        'renderchildmodel'    => Helper\RenderChildModel::class,
        'render_child_model'  => 'renderchildmodel',
        'renderChildModel'    => 'renderchildmodel',
        'RenderChildModel'    => 'renderchildmodel',
        'rendertoplaceholder' => Helper\RenderToPlaceholder::class,
        'renderToPlaceholder' => 'rendertoplaceholder',
        'RenderToPlaceholder' => 'rendertoplaceholder',
        'serverurl'           => Helper\ServerUrl::class,
        'serverUrl'           => 'serverurl',
        'ServerUrl'           => 'serverurl',
        'url'                 => Helper\Url::class,
        'Url'                 => 'url',
        'viewmodel'           => Helper\ViewModel::class,
        'view_model'          => 'viewmodel',
        'viewModel'           => 'viewmodel',
        'ViewModel'           => 'viewmodel',
    ];

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
        'flashmessenger'                  => Helper\Service\FlashMessengerFactory::class,
        'identity'                        => Helper\Service\IdentityFactory::class,
        Helper\BasePath::class            => InvokableFactory::class,
        Helper\Cycle::class               => InvokableFactory::class,
        Helper\DeclareVars::class         => InvokableFactory::class,
        Helper\Doctype::class             => InvokableFactory::class, // overridden by a factory in ViewHelperManagerFactory
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
     * @param  ContainerInterface $container
     * @param  Helper\HelperInterface $helper
     * @return void
     */
    public function injectRenderer(ContainerInterface $container, $helper)
    {
        $renderer = $this->getRenderer();
        if (null === $renderer) {
            return;
        }
        $helper->setView($renderer);
    }

    /**
     * Inject a helper instance with the registered translator
     *
     * @param  ContainerInterface $container
     * @param  Helper\HelperInterface $helper
     * @return void
     */
    public function injectTranslator(ContainerInterface $container, $helper)
    {
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
}
