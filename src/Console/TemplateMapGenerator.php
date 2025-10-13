<?php

declare(strict_types=1);

namespace Laminas\View\Console;

use Laminas\View\Exception\InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use function array_pop;
use function array_slice;
use function assert;
use function basename;
use function dirname;
use function explode;
use function implode;
use function is_dir;
use function is_readable;
use function is_string;
use function is_writable;
use function ltrim;
use function realpath;
use function sort;
use function sprintf;
use function str_ends_with;
use function str_repeat;
use function str_replace;
use function str_starts_with;
use function strlen;
use function strtolower;
use function substr;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;

/**
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final class TemplateMapGenerator
{
    public readonly string $directoryToScan;
    public readonly string $destinationFilePath;

    public function __construct(
        string $directoryToScan,
        string $destinationFilePath,
        public readonly string $fileSuffix = 'phtml',
    ) {
        $directory = realpath($directoryToScan);
        if (! is_string($directory) || ! is_dir($directory) || ! is_readable($directory)) {
            throw new InvalidArgumentException(sprintf(
                'The given template directory does not exist, or it is not readable: %s',
                $directoryToScan,
            ));
        }

        $this->directoryToScan = $directory;

        if (! str_ends_with($destinationFilePath, '.php')) {
            throw new InvalidArgumentException('A .php file name extension is required for the target file');
        }

        $destinationDirectory = realpath(dirname($destinationFilePath));

        if (! is_string($destinationDirectory) || ! is_writable($destinationDirectory)) {
            throw new InvalidArgumentException(sprintf(
                'The target directory is not writable: %s',
                dirname($destinationFilePath),
            ));
        }

        $this->destinationFilePath = $destinationDirectory . DIRECTORY_SEPARATOR . basename($destinationFilePath);
    }

    public function __invoke(): string
    {
        $relativePath = $this->computeRelativePath();
        $map          = [];
        foreach ($this->findTemplates() as $template) {
            $map[] = sprintf(
                "%s'%s' => __DIR__ . '%s%s%s',",
                str_repeat(' ', 12),
                $this->name($template),
                DIRECTORY_SEPARATOR,
                str_repeat('../', $relativePath['ascend']),
                ltrim(str_replace($relativePath['ancestor'], '', $template), DIRECTORY_SEPARATOR),
            );
        }

        $entries = implode(PHP_EOL, $map);

        return <<<PHP
            <?php
            declare(strict_types=1);
            
            return [
                'templates' => [
                    'map' => [
            {$entries}
                    ],
                ],
            ];
            
            PHP;
    }

    /**
     * @return array{
     *     ancestor: string,
     *     ascend: int,
     * }
     */
    private function computeRelativePath(): array
    {
        $dir = dirname($this->destinationFilePath);
        $up  = 0;
        while (str_starts_with($this->directoryToScan, $dir) === false) {
            $dir = implode(DIRECTORY_SEPARATOR, array_slice(explode(DIRECTORY_SEPARATOR, $dir), 0, -1));
            $up++;
        }

        assert($dir !== '');

        return [
            'ancestor' => $dir,
            'ascend'   => $up,
        ];
    }

    /**
     * Create a 'name' for the template based on its file path
     *
     * @param non-empty-string $file
     */
    private function name(string $file): string
    {
        $node     = explode(DIRECTORY_SEPARATOR, str_replace($this->directoryToScan, '', $file));
        $lastPart = array_pop($node);
        $lastPart = substr($lastPart, 0, 0 - (strlen($this->fileSuffix) + 1));

        return ltrim(implode('/', $node) . '/' . $lastPart, '/');
    }

    /** @return list<non-empty-string> */
    private function findTemplates(): array
    {
        $rdi = new RecursiveDirectoryIterator(
            $this->directoryToScan,
            RecursiveDirectoryIterator::FOLLOW_SYMLINKS | RecursiveDirectoryIterator::SKIP_DOTS,
        );
        $rii = new RecursiveIteratorIterator($rdi, RecursiveIteratorIterator::LEAVES_ONLY);

        $files = [];
        foreach ($rii as $file) {
            assert($file instanceof SplFileInfo);
            if (strtolower($file->getExtension()) !== $this->fileSuffix) {
                continue;
            }

            $realPath = $file->getRealPath();
            assert($realPath !== false);

            $files[] = $realPath;
        }

        sort($files);

        return $files;
    }
}
