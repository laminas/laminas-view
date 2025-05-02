<?php

declare(strict_types=1);

namespace Laminas\View\Resolver;

use Laminas\Stdlib\SplStack;
use Laminas\View\Exception;
use Laminas\View\Exception\DomainException;
use SplFileInfo;

use function assert;
use function count;
use function file_exists;
use function is_array;
use function ltrim;
use function pathinfo;
use function preg_match;
use function rtrim;
use function str_starts_with;

use const DIRECTORY_SEPARATOR;
use const PATHINFO_EXTENSION;

/**
 * Resolves view scripts based on a stack of paths
 *
 * @psalm-type PathStack = SplStack<non-empty-string>
 * @psalm-type Options = array{
 *     lfi_protection?: bool,
 *     script_paths?: list<non-empty-string>,
 *     default_suffix?: non-empty-string,
 * }
 */
final class TemplatePathStack implements ResolverInterface
{
    /**
     * Default suffix to use
     *
     * Appends this suffix if the template requested does not use it.
     *
     * @var non-empty-string
     */
    private string $defaultSuffix;

    /** @var PathStack */
    private SplStack $paths;

    /**
     * Flag indicating whether LFI protection for rendering view scripts is enabled
     */
    private bool $lfiProtectionOn;

    /** @param Options $options */
    public function __construct(array $options = [])
    {
        $suffix = ltrim($options['default_suffix'] ?? 'phtml', '.');
        assert($suffix !== '');
        $this->defaultSuffix   = $suffix;
        $this->lfiProtectionOn = $options['lfi_protection'] ?? true;

        /** @psalm-var PathStack $paths */
        $paths       = new SplStack();
        $this->paths = $paths;

        $addPaths = $options['script_paths'] ?? null;
        if (is_array($addPaths)) {
            $this->addPaths($addPaths);
        }
    }

    /**
     * Add many paths to the stack at once
     *
     * @param  list<non-empty-string> $paths
     * @return $this
     */
    public function addPaths(array $paths): self
    {
        foreach ($paths as $path) {
            $this->addPath($path);
        }
        return $this;
    }

    /**
     * Reset the path stack to the paths provided
     *
     * @param list<non-empty-string> $paths
     * @return TemplatePathStack
     * @throws Exception\InvalidArgumentException
     */
    public function setPaths(array $paths): self
    {
        $this->clearPaths();
        $this->addPaths($paths);

        return $this;
    }

    /**
     * Normalize a path for insertion in the stack
     *
     * @return non-empty-string
     */
    private static function normalizePath(string $path): string
    {
        $path  = rtrim($path, '/\\');
        $path .= DIRECTORY_SEPARATOR;

        return $path;
    }

    /**
     * Add a single path to the stack
     *
     * @param non-empty-string $path
     * @return $this
     */
    public function addPath(string $path): self
    {
        $this->paths->push(self::normalizePath($path));

        return $this;
    }

    /**
     * Clear all paths
     */
    public function clearPaths(): void
    {
        /** @psalm-var PathStack $paths */
        $paths       = new SplStack();
        $this->paths = $paths;
    }

    /**
     * Returns stack of paths
     *
     * @return PathStack
     */
    public function getPaths(): SplStack
    {
        return $this->paths;
    }

    /**
     * Turn a template name into a possible filename based on configuration
     */
    private function normalizeTemplateName(string $name): string
    {
        // Ensure we have the expected file extension
        if (pathinfo($name, PATHINFO_EXTENSION) === '') {
            $name .= '.' . $this->defaultSuffix;
        }

        return $name;
    }

    /**
     * Retrieve the filesystem path to a view script
     *
     * @throws DomainException If the template requested includes directory traversal and LFI protection is on.
     * @throws TemplateCannotBeFound
     */
    public function resolve(string $name): string
    {
        if ($this->lfiProtectionOn && preg_match('#\.\.[\\\/]#', $name)) {
            throw new DomainException(
                'Requested scripts may not include parent directory traversal ("../", "..\\" notation)',
            );
        }

        if (! count($this->paths)) {
            throw TemplateCannotBeFound::byName($name);
        }

        $name = $this->normalizeTemplateName($name);
        $path = $this->resolveToPath($name);
        if ($path !== false) {
            return $path;
        }

        throw TemplateCannotBeFound::byName($name);
    }

    public function has(string $name): bool
    {
        return $this->resolveToPath($this->normalizeTemplateName($name)) !== false;
    }

    /** @return non-empty-string|false */
    private function resolveToPath(string $name): string|false
    {
        foreach ($this->paths as $path) {
            $file = new SplFileInfo($path . $name);
            if ($file->isReadable()) {
                // Found! Return it.
                $filePath = $file->getRealPath();
                if ($filePath === false && str_starts_with($path, 'phar://')) {
                    // Do not try to expand phar paths (realpath + phars == fail)
                    $filePath = $path . $name;
                    if (! file_exists($filePath)) {
                        break;
                    }
                }

                return $filePath;
            }
        }

        return false;
    }
}
