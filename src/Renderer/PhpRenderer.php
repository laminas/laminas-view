<?php

declare(strict_types=1);

namespace Laminas\View\Renderer;

use ArrayAccess;
use Laminas\View\Exception;
use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\HelperInterface;
use Laminas\View\Helper\Placeholder\Position;
use Laminas\View\Helper\ViewModel;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ModelInterface as Model;
use Laminas\View\Renderer\RendererInterface as Renderer;
use Laminas\View\Resolver\ResolverInterface;
use Laminas\View\Resolver\ResolverInterface as Resolver;
use Laminas\View\Variables;
use Throwable;
use Traversable;

use function array_key_exists;
use function array_pop;
use function assert;
use function call_user_func_array;
use function extract;
use function get_debug_type;
use function is_array;
use function is_callable;
use function is_string;
use function method_exists;
use function ob_end_clean;
use function ob_get_clean;
use function ob_start;
use function sprintf;

// @codingStandardsIgnoreStart
/**
 * Class for Laminas\View\Strategy\PhpRendererStrategy to help enforce private constructs.
 *
 * Note: all private variables in this class are prefixed with "__". This is to
 * mark them as part of the internal implementation, and thus prevent conflict
 * with variables injected into the renderer.
 *
 * Convenience methods for built-in helpers (@see __call):
 *
 * @method string asset($asset)
 * @method string|null basePath(string|null $file = null)
 * @method \Laminas\View\Helper\Cycle cycle(array $data = [], string $name = 'default')
 * @method Doctype doctype()
 * @method mixed escapeCss(mixed $value, int $recurse = \Laminas\View\Helper\Escaper\AbstractHelper::RECURSE_NONE)
 * @method mixed escapeHtml(mixed $value, int $recurse = \Laminas\View\Helper\Escaper\AbstractHelper::RECURSE_NONE)
 * @method mixed escapeHtmlAttr(mixed $value, int $recurse = \Laminas\View\Helper\Escaper\AbstractHelper::RECURSE_NONE)
 * @method mixed escapeJs(mixed $value, int $recurse = \Laminas\View\Helper\Escaper\AbstractHelper::RECURSE_NONE)
 * @method mixed escapeUrl(mixed $value, int $recurse = \Laminas\View\Helper\Escaper\AbstractHelper::RECURSE_NONE)
 * @method \Laminas\View\Helper\HeadLink headLink(array|null $attributes = null)
 * @method \Laminas\View\Helper\HeadMeta headMeta(string|null $name = null, string|null $content = null, array $attributes = [])
 * @method \Laminas\View\Helper\HeadScript headScript()
 * @method \Laminas\View\Helper\HeadStyle headStyle(string|null $content = null, array $attributes = [], Position $position = Position::Append)
 * @method \Laminas\View\Helper\HeadTitle headTitle(string|null $title = null)
 * @method \Laminas\View\HtmlAttributesSet htmlAttributes(iterable $attributes = [])
 * @method string htmlList(array $items, bool $ordered = false, array|null $attribs = null, bool $escape = true)
 * @method string htmlObject(string $data, string $type, array $attributes = [], array $params = [], string|null $content = null)
 * @method mixed|null identity()
 * @method \Laminas\View\Helper\InlineScript inlineScript()
 * @method Model|\Laminas\View\Helper\Layout layout(string|null $template = null)
 * @method string|\Laminas\View\Helper\Partial partial(string|Model|null $name = null, iterable|object|null $values = null)
 * @method string|\Laminas\View\Helper\PartialLoop partialLoop(string|null $name = null, iterable|object $values = [])
 * @method \Laminas\View\Helper\Placeholder placeholder(string|null $placeholder = null)
 * @method string renderChildModel(string $child)
 * @method void renderToPlaceholder(string|Model $script, string $placeholder)
 * @method \Laminas\View\Helper\ViewModel viewModel()
 * @method string gravatarImage(string $emailAddress, int $imageSize = 80, array $imageAttributes = [], string $defaultImage = 'mm', string $rating = 'g')
 *
 * @final
 */
class PhpRenderer implements Renderer, TreeRendererInterface
{
    /**
     * @var string Rendered content
     */
    private string $__content = '';

    /**
     * @var bool Whether to render trees of view models
     */
    private $__renderTrees = false;

    /**
     * Template being rendered
     *
     * @var null|string
     */
    private $__template;

    /**
     * Queue of templates to render
     * @var array
     */
    private $__templates = [];

    /**
     * Script file name to execute
     *
     * @var string|null
     */
    private $__file;

    /**
     * @var (callable(string): string)|null
     */
    private $__filter;

    /**
     * @var Variables|null
     */
    private $__vars;

    /**
     * @var array Temporary variable stack; used when variables passed to render()
     */
    private $__varsCache = [];
    /** @codingStandardsIgnoreEnd */

    /**
     * @todo handle passing variables object, options
     * @todo handle passing resolver object, options
     */
    public function __construct(
        private readonly HelperPluginManager $pluginManager,
        private readonly ResolverInterface $templateResolver,
    ) {
    }

    /**
     * Return the template engine object
     *
     * Returns the object instance, as it is its own template engine
     *
     * @return PhpRenderer
     */
    public function getEngine()
    {
        return $this;
    }

    /**
     * Retrieve template name or template resolver
     *
     * @param non-empty-string|null $name
     * @return ($name is null ? Resolver : non-empty-string|false)
     */
    public function resolver(string|null $name = null): string|Resolver|false
    {
        if ($name !== null) {
            return $this->templateResolver->resolve($name);
        }

        return $this->templateResolver;
    }

    /**
     * Set variable storage
     *
     * Expects either an array, or an object implementing ArrayAccess.
     *
     * @param  array<string, mixed>|ArrayAccess<string, mixed> $variables
     * @return PhpRenderer
     * @throws Exception\InvalidArgumentException
     */
    public function setVars($variables)
    {
        if (! is_array($variables) && ! $variables instanceof ArrayAccess) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Expected array or ArrayAccess object; received "%s"',
                get_debug_type($variables),
            ));
        }

        // Enforce a Variables container
        if (! $variables instanceof Variables) {
            $variablesAsArray = [];
            foreach ($variables as $key => $value) {
                $variablesAsArray[$key] = $value;
            }
            $variables = new Variables($variablesAsArray);
        }

        $this->__vars = $variables;
        return $this;
    }

    /**
     * Get a single variable, or all variables
     *
     * @param string|null $key
     * @return ($key is null ? Variables : mixed)
     */
    public function vars($key = null)
    {
        if (null === $this->__vars) {
            $this->setVars(new Variables());
        }

        assert($this->__vars !== null);

        if (null === $key) {
            return $this->__vars;
        }
        return $this->__vars[$key];
    }

    /**
     * Get a single variable
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        if (null === $this->__vars) {
            $this->setVars(new Variables());
        }

        return $this->__vars[$key];
    }

    /**
     * Overloading: proxy to Variables container
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        $vars = $this->vars();
        return $vars[$name];
    }

    /**
     * Overloading: proxy to Variables container
     *
     * @param  string $name
     * @param  mixed $value
     * @return void
     */
    public function __set($name, $value)
    {
        $vars        = $this->vars();
        $vars[$name] = $value;
    }

    /**
     * Overloading: proxy to Variables container
     *
     * @param  string $name
     * @return bool
     */
    public function __isset($name)
    {
        $vars = $this->vars();
        return isset($vars[$name]);
    }

    /**
     * Overloading: proxy to Variables container
     *
     * @param  string $name
     * @return void
     */
    public function __unset($name)
    {
        $vars = $this->vars();
        if (! isset($vars[$name])) {
            return;
        }
        unset($vars[$name]);
    }

    /**
     * Get plugin instance
     *
     * @template T
     * @param string|class-string<T> $name Name of plugin to return
     * @return ($name is class-string ? T : HelperInterface|callable)
     */
    public function plugin(string $name): mixed
    {
        return $this->pluginManager->get($name);
    }

    /**
     * Overloading: proxy to helpers
     *
     * Proxies to the attached plugin manager to retrieve, return, and potentially
     * execute helpers.
     *
     * * If the helper does not define __invoke, it will be returned
     * * If the helper does define __invoke, it will be called as a functor
     *
     * @param  string $method
     * @param  array $argv
     * @return HelperInterface|callable|mixed
     */
    public function __call($method, $argv)
    {
        /** @psalm-suppress MixedAssignment $plugin */
        $plugin = $this->plugin($method);

        if (is_callable($plugin)) {
            return call_user_func_array($plugin, $argv);
        }

        return $plugin;
    }

    /**
     * Set a post-rendering filter to apply to the rendered output
     *
     * @param callable(string): string $filter
     */
    public function setFilter(callable $filter): self
    {
        $this->__filter = $filter;

        return $this;
    }

    /**
     * Processes a view script and returns the output.
     *
     * @param  string|Model $nameOrModel Either the template to use, or a
     *                                   ViewModel. The ViewModel must have the
     *                                   template as an option in order to be
     *                                   valid.
     * @param  null|array|Traversable $values Values to use when rendering. If none
     *                                provided, uses those in the composed
     *                                variables container.
     * @return string The script output.
     * @throws Exception\DomainException If a ViewModel is passed, but does not
     *                                   contain a template option.
     * @throws Exception\InvalidArgumentException If the values passed are not
     *                                            an array or ArrayAccess object.
     * @throws Exception\RuntimeException If the template cannot be rendered.
     */
    public function render($nameOrModel, $values = null)
    {
        if ($nameOrModel instanceof Model) {
            $model       = $nameOrModel;
            $nameOrModel = $model->getTemplate();
            if (empty($nameOrModel)) {
                throw new Exception\DomainException(sprintf(
                    '%s: received View Model argument, but template is empty',
                    __METHOD__
                ));
            }
            $options = $model->getOptions();
            foreach ($options as $setting => $value) {
                $method = 'set' . $setting;
                if (method_exists($this, $method)) {
                    $this->$method($value);
                }
                unset($method, $setting, $value);
            }
            unset($options);

            // Give view model awareness via ViewModel helper
            $helper = $this->plugin(ViewModel::class);
            $helper->setCurrent($model);

            $values = $model->getVariables();
            unset($model);
        }

        // find the script file name using the parent private method
        $this->addTemplate($nameOrModel);
        unset($nameOrModel); // remove $name from local scope

        $this->__varsCache[] = $this->vars();

        if (null !== $values) {
            $this->setVars($values);
        }
        unset($values);

        // @codingStandardsIgnoreStart
        /**
         * extract all assigned vars (pre-escaped), but not 'this'.
         * assigns to a double-underscored variable, to prevent naming collisions
         */
        $__vars = $this->vars()->getArrayCopy();
        if (array_key_exists('this', $__vars)) {
            unset($__vars['this']);
        }
        extract($__vars);
        unset($__vars); // remove $__vars from local scope
        // @codingStandardsIgnoreEnd

        $this->__content = '';
        while ($this->__template = array_pop($this->__templates)) {
            $this->__file = $this->resolver($this->__template);
            if (! is_string($this->__file)) {
                throw new Exception\RuntimeException(sprintf(
                    '%s: Unable to render template "%s"; resolver could not resolve to a file',
                    __METHOD__,
                    $this->__template
                ));
            }
            try {
                ob_start();
                $includeReturn   = include $this->__file;
                $this->__content = ob_get_clean();
            } catch (Throwable $ex) {
                ob_end_clean();
                throw $ex;
            }

            if ($includeReturn === false && $this->__content === '') {
                throw new Exception\UnexpectedValueException(sprintf(
                    '%s: Unable to render template "%s"; file include failed',
                    __METHOD__,
                    $this->__file
                ));
            }
        }

        $this->setVars(array_pop($this->__varsCache));

        if ($this->__filter !== null) {
            return ($this->__filter)($this->__content);
        }

        return $this->__content;
    }

    /**
     * Set flag indicating whether or not we should render trees of view models
     *
     * If set to true, the View instance will not attempt to render children
     * separately, but instead pass the root view model directly to the PhpRenderer.
     * It is then up to the developer to render the children from within the
     * view script.
     *
     * @param  bool $renderTrees
     * @return PhpRenderer
     */
    public function setCanRenderTrees($renderTrees)
    {
        $this->__renderTrees = (bool) $renderTrees;
        return $this;
    }

    /**
     * Can we render trees, or are we configured to do so?
     *
     * @return bool
     */
    public function canRenderTrees()
    {
        return $this->__renderTrees;
    }

    /**
     * Add a template to the stack
     *
     * @param  string $template
     * @return PhpRenderer
     */
    public function addTemplate($template)
    {
        $this->__templates[] = $template;
        return $this;
    }

    /**
     * Make sure View variables are cloned when the view is cloned.
     *
     * @return void
     */
    public function __clone()
    {
        $this->__vars = clone $this->vars();
    }
}
