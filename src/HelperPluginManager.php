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
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 * @psalm-import-type FactoriesConfigurationType from ConfigInterface
 */
class HelperPluginManager extends AbstractPluginManager
{
    /** @var array<string, string>|array<array-key, string> */
    protected $aliases;

    /** @var FactoriesConfigurationType */
    protected $factories;

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
        $this->aliases   = ConfigProvider::defaultViewHelperAliases();
        $this->factories = ConfigProvider::defaultViewHelperFactories();

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
            throw new InvalidHelperException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
