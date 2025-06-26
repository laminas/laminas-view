<?php

declare(strict_types=1);

namespace Laminas\View\Renderer;

use ArrayIterator;
use IteratorAggregate;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\View\Exception\RenderingFailedException;
use Laminas\View\Helper\HelperInterface;
use Laminas\View\HelperPluginManagerInterface;
use Throwable;
use Traversable;

use function array_key_exists;
use function assert;
use function extract;
use function is_callable;
use function is_string;
use function ob_end_clean;
use function ob_get_clean;
use function ob_start;

/**
 * phpcs:disable WebimpressCodingStandard.NamingConventions.ValidVariableName
 * phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 * @psalm-no-seal-properties Magic properties are retrieved from this class to fetch view variables
 * @implements IteratorAggregate<string, mixed>
 * @psalm-api
 */
final class Template implements IteratorAggregate
{
    private bool $__renderLock = false;

    /**
     * @param non-empty-string $__template
     * @param array<string, mixed> $__variables
     */
    public function __construct(
        private readonly string $__template,
        private readonly array $__variables,
        private readonly HelperPluginManagerInterface $__plugins,
        private readonly bool $__strictVariables = true,
    ) {
    }

    /** @throws RenderingFailedException If there are any errors or violations during rendering. */
    public function __invoke(): string
    {
        // This variable locks rendering of _this_ instance and prevents calls to $this->__invoke() from causing
        // infinite loops.
        if ($this->__renderLock) {
            throw RenderingFailedException::becauseARenderLoopHasBeenDetected($this->__template);
        }

        try {
            $this->__renderLock = true;
            ob_start();

            // Extract variables into local scope
            $__localVars = $this->__variables;
            extract($__localVars); // phpcs:ignore Generic.PHP.ForbiddenFunctions
            unset($__localVars);

            /**
             * @psalm-var mixed $include
             * @psalm-suppress UnresolvableInclude
             */
            $include = include $this->__template;
            if ($include === false) {
                throw RenderingFailedException::becauseTheTemplateFileCouldNotBeIncluded($this->__template);
            }

            $content = ob_get_clean();
            assert(is_string($content));
            $this->__renderLock = false;

            return $content;
        } catch (Throwable $error) {
            ob_end_clean();

            if ($error instanceof RenderingFailedException) {
                throw $error; // Do not wrap our own exceptions
            }

            throw RenderingFailedException::becauseOfAnException($this->__template, $error);
        }
    }

    /**
     * Allows variable retrieval only from the composed array of variables
     *
     * @param non-empty-string $name
     */
    public function __get(string $name): mixed
    {
        if (! array_key_exists($name, $this->__variables) && $this->__strictVariables) {
            throw RenderingFailedException::becauseOfAccessToAnUndeclaredVariable($name, $this->__template);
        }

        return $this->__variables[$name] ?? null;
    }

    /**
     * Prevent overloading of member variables
     *
     * @param non-empty-string $name
     */
    public function __set(string $name, mixed $_value): never
    {
        throw RenderingFailedException::becauseMemberVariablesCannotBeMutated($name, $this->__template);
    }

    public function __isset(string $name): bool
    {
        return isset($this->__variables[$name]);
    }

    /**
     * Proxies calls to unknown methods to the helper plugin manager
     *
     * @param non-empty-string $method
     * @param array<non-empty-string, mixed> $args
     */
    public function __call(string $method, array $args): mixed
    {
        try {
            $plugin = $this->__plugins->get($method);
        } catch (ServiceNotFoundException $e) {
            throw RenderingFailedException::becauseOfAnUnknownPlugin($method, $this->__template, $e);
        }

        assert(is_callable($plugin) || $plugin instanceof HelperInterface);

        try {
            /** @psalm-var mixed $returnValue */
            $returnValue = is_callable($plugin)
                ? $plugin(...$args)
                : $plugin;

            return $returnValue;
        } catch (Throwable $e) {
            throw RenderingFailedException::becauseOfAPluginException($method, $e);
        }
    }

    /** @return Traversable<string, mixed> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->__variables);
    }
}
