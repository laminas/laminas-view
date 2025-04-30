<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Laminas\View\Exception\RuntimeException;

use function ltrim;
use function rtrim;
use function sprintf;

/**
 * Helper for retrieving the base path.
 */
final class BasePath implements StatefulHelperInterface
{
    private string|null $basePath;
    private string|null $configuredBasePath;

    public function __construct(string|null $basePath = null)
    {
        if ($basePath !== null) {
            $basePath = rtrim($basePath, '/');
        }

        $this->basePath           = $basePath;
        $this->configuredBasePath = $basePath;
    }

    public function resetState(): void
    {
        $this->basePath = $this->configuredBasePath;
    }

    /**
     * Returns site's base path, or file with base path prepended.
     *
     * $file is appended to the base path for simplicity.
     *
     * @throws RuntimeException
     */
    public function __invoke(string|null $file = null): string
    {
        if ($this->basePath === null) {
            throw new RuntimeException('No base path provided');
        }

        if ($file !== null && $file !== '') {
            return sprintf(
                '%s/%s',
                $this->basePath,
                ltrim($file, '/'),
            );
        }

        return $this->basePath;
    }

    /**
     * Set the base path.
     */
    public function setBasePath(string $basePath): self
    {
        $this->basePath = rtrim($basePath, '/');
        return $this;
    }
}
