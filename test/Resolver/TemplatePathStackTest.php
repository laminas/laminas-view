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
use function array_unshift;
use function realpath;

use const DIRECTORY_SEPARATOR;

/**
 * @psalm-import-type Options from TemplatePathStack
 */
final class TemplatePathStackTest extends TestCase
{
    private TemplatePathStack $stack;

    /** @var non-empty-string */
    private string $baseDir;

    protected function setUp(): void
    {
        $dir = realpath(__DIR__ . '/..');
        self::assertNotFalse($dir);
        $this->baseDir = $dir . '/';
        $this->stack   = new TemplatePathStack();
    }

    public function testAddPathAddsPathToStack(): void
    {
        $this->stack->addPath($this->baseDir);
        $paths = $this->stack->getPaths();
        self::assertCount(1, $paths);
        self::assertEquals($this->baseDir, $paths->pop());
    }

    public function testPathsAreProcessedAsStack(): void
    {
        $paths = [
            $this->baseDir,
            $this->baseDir . '_files/',
        ];
        foreach ($paths as $path) {
            $this->stack->addPath($path);
        }
        $test = $this->stack->getPaths()->toArray();
        self::assertEquals(array_reverse($paths), $test);
    }

    public function testAddPathsAddsPathsToStack(): void
    {
        $this->stack->addPath($this->baseDir . 'Helper/');
        $paths = [
            $this->baseDir,
            $this->baseDir . '_files/',
        ];
        $this->stack->addPaths($paths);
        array_unshift($paths, $this->baseDir . 'Helper/');
        self::assertEquals(array_reverse($paths), $this->stack->getPaths()->toArray());
    }

    public function testSetPathsOverwritesStack(): void
    {
        $this->stack->addPath($this->baseDir . 'Helper/');
        $paths = [
            $this->baseDir,
            $this->baseDir . '_files/',
        ];
        $this->stack->setPaths($paths);
        self::assertEquals(array_reverse($paths), $this->stack->getPaths()->toArray());
    }

    public function testClearPathsClearsStack(): void
    {
        $paths = [
            $this->baseDir,
            $this->baseDir . '_files/',
        ];
        $this->stack->setPaths($paths);
        $this->stack->clearPaths();
        self::assertEquals(0, $this->stack->getPaths()->count());
    }

    public function testDoesNotAllowParentDirectoryTraversalByDefault(): void
    {
        $this->stack->addPath($this->baseDir . '_templates/');

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('parent directory traversal');
        $this->stack->resolve('../_stubs/scripts/LfiProtectionCheck.phtml');
    }

    public function testDisablingLfiProtectionAllowsParentDirectoryTraversal(): void
    {
        $stack = new TemplatePathStack([
            'lfi_protection' => false,
            'script_paths'   => [
                $this->baseDir . '_templates/',
            ],
        ]);

        $test = $stack->resolve('../_stubs/scripts/LfiProtectionCheck.phtml');
        self::assertIsString($test);
        self::assertStringContainsString('LfiProtectionCheck.phtml', $test);
    }

    public function testReturnsFalseWhenRetrievingScriptIfNoPathsRegistered(): void
    {
        self::assertFalse($this->stack->resolve('test.phtml'));
    }

    public function testReturnsFalseWhenUnableToResolveScriptToPath(): void
    {
        $this->stack->addPath($this->baseDir . '_templates/');
        self::assertFalse($this->stack->resolve('bogus-script.txt'));
    }

    public function testReturnsFullPathNameWhenAbleToResolveScriptPath(): void
    {
        $this->stack->addPath($this->baseDir . '_templates/');
        $expected = realpath($this->baseDir . '_templates/test.phtml');
        $test     = $this->stack->resolve('test.phtml');
        self::assertSame($expected, $test);
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
        $path = 'phar://' . $this->baseDir
            . '_templates'
            . DIRECTORY_SEPARATOR . 'view.phar'
            . DIRECTORY_SEPARATOR . 'start'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . 'views'
            . DIRECTORY_SEPARATOR;

        $this->stack->addPath($path);
        $test = $this->stack->resolve('foo' . DIRECTORY_SEPARATOR . 'hello.phtml');
        self::assertEquals($path . 'foo' . DIRECTORY_SEPARATOR . 'hello.phtml', $test);
    }

    public function testSettingDefaultSuffixStripsLeadingDot(): void
    {
        $stack = new TemplatePathStack([
            'default_suffix' => '.phtml',
            'script_paths'   => [
                $this->baseDir . '_templates',
            ],
        ]);

        $result = $stack->resolve('test');
        self::assertNotFalse($result);
        self::assertStringEndsWith('test.phtml', $result);
    }
}
