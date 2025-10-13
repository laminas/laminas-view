<?php

declare(strict_types=1);

namespace LaminasTest\View\Resolver;

use Laminas\View\Exception\DomainException;
use Laminas\View\Resolver\TemplatePathStack;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use TypeError;

use function array_reverse;

/**
 * @psalm-import-type Options from TemplatePathStack
 */
final class TemplatePathStackTest extends TestCase
{
    public function testAddPathAddsPathToStack(): void
    {
        $resolver = new TemplatePathStack([
            'script_paths'   => [
                __DIR__ . '/template-path-stack/a/',
            ],
            'default_suffix' => 'phtml',
        ]);

        $resolver->addPath(__DIR__ . '/template-path-stack/b/');
        $paths = $resolver->getPaths()->toArray();

        self::assertSame(
            [
                __DIR__ . '/template-path-stack/b/',
                __DIR__ . '/template-path-stack/a/',
            ],
            $paths,
        );
    }

    public function testPathsAreProcessedAsStack(): void
    {
        $resolver = new TemplatePathStack([
            'script_paths'   => [
                __DIR__ . '/template-path-stack/a',
                __DIR__ . '/template-path-stack/b',
            ],
            'default_suffix' => 'phtml',
        ]);

        $path = $resolver->resolve('example');

        self::assertSame(__DIR__ . '/template-path-stack/b/example.phtml', $path);
    }

    public function testAddPathsAddsPathsToStack(): void
    {
        $paths = [
            __DIR__ . '/template-path-stack/a/',
            __DIR__ . '/template-path-stack/b/',
        ];

        $resolver = new TemplatePathStack([
            'script_paths'   => [],
            'default_suffix' => 'phtml',
        ]);

        $resolver->addPaths($paths);
        self::assertEquals(array_reverse($paths), $resolver->getPaths()->toArray());
    }

    public function testSetPathsOverwritesStack(): void
    {
        $paths = [
            __DIR__ . '/template-path-stack/a/',
            __DIR__ . '/template-path-stack/b/',
        ];

        $resolver = new TemplatePathStack([
            'script_paths'   => [
                __DIR__ . '/template-path-stack/not-there/',
            ],
            'default_suffix' => 'phtml',
        ]);

        $resolver->setPaths($paths);
        self::assertEquals(array_reverse($paths), $resolver->getPaths()->toArray());
    }

    public function testClearPathsClearsStack(): void
    {
        $resolver = new TemplatePathStack([
            'script_paths'   => [
                __DIR__ . '/template-path-stack/a/',
                __DIR__ . '/template-path-stack/b/',
            ],
            'default_suffix' => 'phtml',
        ]);

        $resolver->clearPaths();
        self::assertSame([], $resolver->getPaths()->toArray());
    }

    public function testDoesNotAllowParentDirectoryTraversalByDefault(): void
    {
        $resolver = new TemplatePathStack([
            'script_paths'   => [
                __DIR__ . '/template-path-stack/a/',
            ],
            'default_suffix' => 'phtml',
            'lfi_protection' => true,
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('parent directory traversal');
        $resolver->resolve('../traverse/example.phtml');
    }

    public function testDisablingLfiProtectionAllowsParentDirectoryTraversal(): void
    {
        $resolver = new TemplatePathStack([
            'script_paths'   => [
                __DIR__ . '/template-path-stack/a/',
            ],
            'default_suffix' => 'phtml',
            'lfi_protection' => false,
        ]);

        $path = $resolver->resolve('../traverse/example.phtml');

        self::assertSame(
            __DIR__ . '/template-path-stack/traverse/example.phtml',
            $path,
        );
    }

    public function testReturnsFalseWhenRetrievingScriptIfNoPathsRegistered(): void
    {
        $resolver = new TemplatePathStack([]);
        self::assertFalse($resolver->resolve('test.phtml'));
    }

    public function testReturnsFalseWhenUnableToResolveScriptToPath(): void
    {
        $resolver = new TemplatePathStack([
            'script_paths' => [
                __DIR__ . '/template-path-stack/a/',
            ],
        ]);
        self::assertFalse($resolver->resolve('bogus-script.txt'));
    }

    /**
     * @psalm-return array<array-key, array{0: mixed}>
     */
    public static function invalidOptions(): array
    {
        return [
            [true],
            [1],
            [1.0],
            ['foo'],
            [new stdClass()],
        ];
    }

    #[DataProvider('invalidOptions')]
    public function testSettingOptionsWithInvalidArgumentRaisesException(mixed $options): void
    {
        $this->expectException(TypeError::class);
        /** @psalm-suppress MixedArgument */
        new TemplatePathStack($options);
    }

    public function testAllowsRelativePharPath(): void
    {
        $path     = __DIR__ . '/template-path-stack/a/view.phar/start/../views/';
        $path     = 'phar://' . $path;
        $resolver = new TemplatePathStack([]);
        $resolver->addPath($path);

        $test = $resolver->resolve('foo/hello.phtml');
        self::assertSame($path . 'foo/hello.phtml', $test);
    }

    public function testSettingDefaultSuffixStripsLeadingDot(): void
    {
        $stack = new TemplatePathStack([
            'default_suffix' => '.phtml',
            'script_paths'   => [
                __DIR__ . '/template-path-stack/a/',
            ],
        ]);

        $result = $stack->resolve('example');
        self::assertNotFalse($result);
        self::assertStringEndsWith('example.phtml', $result);
    }

    public function testResolveFilesWithDifferentExtensions(): void
    {
        $resolver = new TemplatePathStack([
            'default_suffix' => '.phtml',
            'script_paths'   => [
                __DIR__ . '/template-path-stack/a/',
            ],
        ]);

        self::assertSame(__DIR__ . '/template-path-stack/a/example.phtml', $resolver->resolve('example'));
        self::assertSame(__DIR__ . '/template-path-stack/a/example.txt', $resolver->resolve('example.txt'));
    }

    public function testResolveFilesInSubDirectories(): void
    {
        $resolver = new TemplatePathStack([
            'default_suffix' => '.phtml',
            'script_paths'   => [
                __DIR__ . '/template-path-stack',
            ],
        ]);

        self::assertSame(__DIR__ . '/template-path-stack/a/example.phtml', $resolver->resolve('a/example'));
        self::assertSame(__DIR__ . '/template-path-stack/b/example.phtml', $resolver->resolve('b/example.phtml'));
    }
}
