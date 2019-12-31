<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View;

use Laminas\I18n\Translator\TranslatorAwareInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\ConfigInterface;

/**
 * Plugin manager implementation for view helpers
 *
 * Enforces that helpers retrieved are instances of
 * Helper\HelperInterface. Additionally, it registers a number of default
 * helpers.
 *
 * @category   Laminas
 * @package    Laminas_View
 */
class HelperPluginManager extends AbstractPluginManager
{
    /**
     * Default set of helpers
     *
     * @var array
     */
    protected $invokableClasses = array(
        // basepath, doctype, and url are set up as factories in the ViewHelperManagerFactory.
        // basepath and url are not very useful without their factories, however the doctype
        // helper works fine as an invokable. The factory for doctype simply checks for the
        // config value from the merged config.
        'doctype'             => 'Laminas\View\Helper\Doctype', // overridden by a factory in ViewHelperManagerFactory
        'basepath'            => 'Laminas\View\Helper\BasePath',
        'url'                 => 'Laminas\View\Helper\Url',
        'cycle'               => 'Laminas\View\Helper\Cycle',
        'declarevars'         => 'Laminas\View\Helper\DeclareVars',
        'escapehtml'          => 'Laminas\View\Helper\EscapeHtml',
        'escapehtmlattr'      => 'Laminas\View\Helper\EscapeHtmlAttr',
        'escapejs'            => 'Laminas\View\Helper\EscapeJs',
        'escapecss'           => 'Laminas\View\Helper\EscapeCss',
        'escapeurl'           => 'Laminas\View\Helper\EscapeUrl',
        'gravatar'            => 'Laminas\View\Helper\Gravatar',
        'headlink'            => 'Laminas\View\Helper\HeadLink',
        'headmeta'            => 'Laminas\View\Helper\HeadMeta',
        'headscript'          => 'Laminas\View\Helper\HeadScript',
        'headstyle'           => 'Laminas\View\Helper\HeadStyle',
        'headtitle'           => 'Laminas\View\Helper\HeadTitle',
        'htmlflash'           => 'Laminas\View\Helper\HtmlFlash',
        'htmllist'            => 'Laminas\View\Helper\HtmlList',
        'htmlobject'          => 'Laminas\View\Helper\HtmlObject',
        'htmlpage'            => 'Laminas\View\Helper\HtmlPage',
        'htmlquicktime'       => 'Laminas\View\Helper\HtmlQuicktime',
        'inlinescript'        => 'Laminas\View\Helper\InlineScript',
        'json'                => 'Laminas\View\Helper\Json',
        'layout'              => 'Laminas\View\Helper\Layout',
        'paginationcontrol'   => 'Laminas\View\Helper\PaginationControl',
        'partialloop'         => 'Laminas\View\Helper\PartialLoop',
        'partial'             => 'Laminas\View\Helper\Partial',
        'placeholder'         => 'Laminas\View\Helper\Placeholder',
        'renderchildmodel'    => 'Laminas\View\Helper\RenderChildModel',
        'rendertoplaceholder' => 'Laminas\View\Helper\RenderToPlaceholder',
        'serverurl'           => 'Laminas\View\Helper\ServerUrl',
        'viewmodel'           => 'Laminas\View\Helper\ViewModel',
    );

    /**
     * @var Renderer\RendererInterface
     */
    protected $renderer;

    /**
     * Constructor
     *
     * After invoking parent constructor, add an initializer to inject the
     * attached renderer and translator, if any, to the currently requested helper.
     *
     * @param  null|ConfigInterface $configuration
     */
    public function __construct(ConfigInterface $configuration = null)
    {
        parent::__construct($configuration);
        $this->addInitializer(array($this, 'injectRenderer'))
             ->addInitializer(array($this, 'injectTranslator'));
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
     * @param  Helper\HelperInterface $helper
     * @return void
     */
    public function injectRenderer($helper)
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
     * @param  Helper\HelperInterface $helper
     * @return void
     */
    public function injectTranslator($helper)
    {
        if ($helper instanceof TranslatorAwareInterface) {
            $locator = $this->getServiceLocator();
            if ($locator && $locator->has('translator')) {
                $helper->setTranslator($locator->get('translator'));
            }
        }
    }

    /**
     * Validate the plugin
     *
     * Checks that the helper loaded is an instance of Helper\HelperInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\InvalidHelperException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof Helper\HelperInterface) {
            // we're okay
            return;
        }

        throw new Exception\InvalidHelperException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Helper\HelperInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}
