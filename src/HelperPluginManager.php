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
use Zend\Stdlib\ArrayUtils;

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

    protected $config = [
        'aliases' => [
            'basePath'            => 'basepath',
            'BasePath'            => 'basepath',
            'Cycle'               => 'cycle',
            'declareVars'         => 'declarevars',
            'DeclareVars'         => 'declarevars',
            'Doctype'             => 'doctype',
            'flashMessenger'      => 'flashmessenger',
            'FlashMessenger'      => 'flashmessenger',
            'escapeHtml'          => 'escapehtml',
            'EscapeHtml'          => 'escapehtml',
            'escapeHtmlAttr'      => 'escapehtmlattr',
            'EscapeHtmlAttr'      => 'escapehtmlattr',
            'escapeJs'            => 'escapejs',
            'EscapeJs'            => 'escapejs',
            'escapeCss'           => 'escapecss',
            'EscapeCss'           => 'escapecss',
            'escapeUrl'           => 'escapeurl',
            'EscapeUrl'           => 'escapeurl',
            'Gravatar'            => 'gravatar',
            'htmlTag'             => 'htmltag',
            'HtmlTag'             => 'htmltag',
            'headLink'            => 'headlink',
            'HeadLink'            => 'headlink',
            'headMeta'            => 'headmeta',
            'HeadMeta'            => 'headmeta',
            'headScript'          => 'headscript',
            'HeadScript'          => 'headscript',
            'headStyle'           => 'headstyle',
            'HeadStyle'           => 'headstyle',
            'headTitle'           => 'headtitle',
            'HeadTitle'           => 'headtitle',
            'htmlFlash'           => 'htmlflash',
            'HtmlFlash'           => 'htmlflash',
            'htmlList'            => 'htmllist',
            'HtmlList'            => 'htmllist',
            'htmlObject'          => 'htmlobject',
            'HtmlObject'          => 'htmlobject',
            'htmlPage'            => 'htmlpage',
            'HtmlPage'            => 'htmlpage',
            'htmlQuicktime'       => 'htmlquicktime',
            'HtmlQuicktime'       => 'htmlquicktime',
            'Identity'            => 'identity',
            'inlineScript'        => 'inlinescript',
            'InlineScript'        => 'inlinescript',
            'Json'                => 'json',
            'Layout'              => 'layout',
            'paginationControl'   => 'paginationcontrol',
            'PaginationControl'   => 'paginationcontrol',
            'Partial'             => 'partial',
            'partialLoop'         => 'partialloop',
            'PartialLoop'         => 'partialloop',
            'Placeholder'         => 'placeholder',
            'renderChildModel'    => 'renderchildmodel',
            'RenderChildModel'    => 'renderchildmodel',
            'render_child_model'  => 'renderchildmodel',
            'renderToPlaceholder' => 'rendertoplaceholder',
            'RenderToPlaceholder' => 'rendertoplaceholder',
            'serverUrl'           => 'serverurl',
            'ServerUrl'           => 'serverurl',
            'Url'                 => 'url',
            'viewModel'           => 'viewmodel',
            'ViewModel'           => 'viewmodel',
            'view_model'          => 'viewmodel',
        ],
        'factories' => [
            'flashmessenger' => Helper\Service\FlashMessengerFactory::class,
            'identity'       => Helper\Service\IdentityFactory::class,
        ],
        'invokables' => [
            // basepath, doctype, and url are set up as factories in the ViewHelperManagerFactory.
            // basepath and url are not very useful without their factories, however the doctype
            // helper works fine as an invokable. The factory for doctype simply checks for the
            // config value from the merged config.
            'basepath'            => Helper\BasePath::class,
            'cycle'               => Helper\Cycle::class,
            'declarevars'         => Helper\DeclareVars::class,
            'doctype'             => Helper\Doctype::class, // overridden by a factory in ViewHelperManagerFactory
            'escapehtml'          => Helper\EscapeHtml::class,
            'escapehtmlattr'      => Helper\EscapeHtmlAttr::class,
            'escapejs'            => Helper\EscapeJs::class,
            'escapecss'           => Helper\EscapeCss::class,
            'escapeurl'           => Helper\EscapeUrl::class,
            'gravatar'            => Helper\Gravatar::class,
            'htmltag'             => Helper\HtmlTag::class,
            'headlink'            => Helper\HeadLink::class,
            'headmeta'            => Helper\HeadMeta::class,
            'headscript'          => Helper\HeadScript::class,
            'headstyle'           => Helper\HeadStyle::class,
            'headtitle'           => Helper\HeadTitle::class,
            'htmlflash'           => Helper\HtmlFlash::class,
            'htmllist'            => Helper\HtmlList::class,
            'htmlobject'          => Helper\HtmlObject::class,
            'htmlpage'            => Helper\HtmlPage::class,
            'htmlquicktime'       => Helper\HtmlQuicktime::class,
            'inlinescript'        => Helper\InlineScript::class,
            'json'                => Helper\Json::class,
            'layout'              => Helper\Layout::class,
            'paginationcontrol'   => Helper\PaginationControl::class,
            'partialloop'         => Helper\PartialLoop::class,
            'partial'             => Helper\Partial::class,
            'placeholder'         => Helper\Placeholder::class,
            'renderchildmodel'    => Helper\RenderChildModel::class,
            'rendertoplaceholder' => Helper\RenderToPlaceholder::class,
            'serverurl'           => Helper\ServerUrl::class,
            'url'                 => Helper\Url::class,
            'viewmodel'           => Helper\ViewModel::class,
        ],
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
        $this->config['initializers'] = [
            [$this, 'injectRenderer'],
            [$this, 'injectTranslator'],
        ];
        $config = ArrayUtils::merge($this->config, $config);
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
