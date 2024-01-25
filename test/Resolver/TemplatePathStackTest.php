<?php

declare(strict_types=1);

namespace LaminasTest\View\Resolver;

use ArrayObject;
use Laminas\View\Exception;
use Laminas\View\Resolver\TemplatePathStack;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

use function array_reverse;
use function array_unshift;
use function realpath;

use const DIRECTORY_SEPARATOR;

/**
 * @psalm-import-type Options from TemplatePathStack
 */
class TemplatePathStackTest extends TestCase
{
    private TemplatePathStack $stack;

    /** @var list<string> */
    private array $paths;

    private string $baseDir;

    protected function setUp(): void
    {
        $this->baseDir = realpath(__DIR__ . '/..');
        $this->stack   = new TemplatePathStack();
        $this->paths   = [
            TemplatePathStack::normalizePath($this->baseDir),
            TemplatePathStack::normalizePath($this->baseDir . '/_templates'),
        ];
    }

    public function testAddPathAddsPathToStack(): void
    {
        $this->stack->addPath($this->baseDir);
        $paths = $this->stack->getPaths();
        $this->assertCount(1, $paths);
        $this->assertEquals(TemplatePathStack::normalizePath($this->baseDir), $paths->pop());
    }

    public function testPathsAreProcessedAsStack(): void
    {
        $paths = [
            TemplatePathStack::normalizePath($this->baseDir),
            TemplatePathStack::normalizePath($this->baseDir . '/_files'),
        ];
        foreach ($paths as $path) {
            $this->stack->addPath($path);
        }
        $test = $this->stack->getPaths()->toArray();
        $this->assertEquals(array_reverse($paths), $test);
    }

    public function testAddPathsAddsPathsToStack(): void
    {
        $this->stack->addPath($this->baseDir . '/Helper');
        $paths = [
            TemplatePathStack::normalizePath($this->baseDir),
            TemplatePathStack::normalizePath($this->baseDir . '/_files'),
        ];
        $this->stack->addPaths($paths);
        array_unshift($paths, TemplatePathStack::normalizePath($this->baseDir . '/Helper'));
        $this->assertEquals(array_reverse($paths), $this->stack->getPaths()->toArray());
    }

    public function testSetPathsOverwritesStack(): void
    {
        $this->stack->addPath($this->baseDir . '/Helper');
        $paths = [
            TemplatePathStack::normalizePath($this->baseDir),
            TemplatePathStack::normalizePath($this->baseDir . '/_files'),
        ];
        $this->stack->setPaths($paths);
        $this->assertEquals(array_reverse($paths), $this->stack->getPaths()->toArray());
    }

    public function testClearPathsClearsStack(): void
    {
        $paths = [
            $this->baseDir,
            $this->baseDir . '/_files',
        ];
        $this->stack->setPaths($paths);
        $this->stack->clearPaths();
        $this->assertEquals(0, $this->stack->getPaths()->count());
    }

    public function testLfiProtectionEnabledByDefault(): void
    {
        $this->assertTrue($this->stack->isLfiProtectionOn());
    }

    public function testMayDisableLfiProtection(): void
    {
        $this->stack->setLfiProtection(false);
        $this->assertFalse($this->stack->isLfiProtectionOn());
    }

    public function testDoesNotAllowParentDirectoryTraversalByDefault(): void
    {
        $this->stack->addPath($this->baseDir . '/_templates');

        $this->expectException(Exception\ExceptionInterface::class);
        $this->expectExceptionMessage('parent directory traversal');
        $this->stack->resolve('../_stubs/scripts/LfiProtectionCheck.phtml');
    }

    public function testDisablingLfiProtectionAllowsParentDirectoryTraversal(): void
    {
        $this->stack->setLfiProtection(false)
                    ->addPath($this->baseDir . '/_templates');

        $test = $this->stack->resolve('../_stubs/scripts/LfiProtectionCheck.phtml');
        $this->assertStringContainsString('LfiProtectionCheck.phtml', $test);
    }

    public function testReturnsFalseWhenRetrievingScriptIfNoPathsRegistered(): void
    {
        $this->assertFalse($this->stack->resolve('test.phtml'));
        $this->assertEquals(TemplatePathStack::FAILURE_NO_PATHS, $this->stack->getLastLookupFailure());
    }

    public function testReturnsFalseWhenUnableToResolveScriptToPath(): void
    {
        $this->stack->addPath($this->baseDir . '/_templates');
        $this->assertFalse($this->stack->resolve('bogus-script.txt'));
        $this->assertEquals(TemplatePathStack::FAILURE_NOT_FOUND, $this->stack->getLastLookupFailure());
    }

    public function testReturnsFullPathNameWhenAbleToResolveScriptPath(): void
    {
        $this->stack->addPath($this->baseDir . '/_templates');
        $expected = realpath($this->baseDir . '/_templates/test.phtml');
        $test     = $this->stack->resolve('test.phtml');
        $this->assertEquals($expected, $test);
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
        $this->expectException(Exception\ExceptionInterface::class);
        /** @psalm-suppress MixedArgument */
        $this->stack->setOptions($options);
    }

    /**
     * @return array<array-key, array{0: Options|ArrayObject}>
     */
    public static function validOptions(): array
    {
        $options = [
            'lfi_protection' => false,
            'default_suffix' => 'php',
        ];
        return [
            [$options],
            [new ArrayObject($options)],
        ];
    }

    /**
     * @param Options $options
     */
    #[DataProvider('validOptions')]
    public function testAllowsSettingOptions($options): void
    {
        $options['script_paths'] = $this->paths;
        $this->stack->setOptions($options);
        $this->assertFalse($this->stack->isLfiProtectionOn());

        $this->assertSame($options['default_suffix'], $this->stack->getDefaultSuffix());

        $this->assertEquals(array_reverse($this->paths), $this->stack->getPaths()->toArray());
    }

    /**
     * @param Options $options
     */
    #[DataProvider('validOptions')]
    public function testAllowsPassingOptionsToConstructor($options): void
    {
        $options['script_paths'] = $this->paths;
        $stack                   = new TemplatePathStack($options);
        $this->assertFalse($stack->isLfiProtectionOn());

        $this->assertEquals(array_reverse($this->paths), $stack->getPaths()->toArray());
    }

    public function testAllowsRelativePharPath(): void
    {
        $path = 'phar://' . $this->baseDir
            . DIRECTORY_SEPARATOR . '_templates'
            . DIRECTORY_SEPARATOR . 'view.phar'
            . DIRECTORY_SEPARATOR . 'start'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . 'views';

        $this->stack->addPath($path);
        $test = $this->stack->resolve('foo' . DIRECTORY_SEPARATOR . 'hello.phtml');
        $this->assertEquals($path . DIRECTORY_SEPARATOR . 'foo' . DIRECTORY_SEPARATOR . 'hello.phtml', $test);
    }

    public function testDefaultFileSuffixIsPhtml(): void
    {
        $this->assertEquals('phtml', $this->stack->getDefaultSuffix());
    }

    public function testDefaultFileSuffixIsMutable(): void
    {
        $this->stack->setDefaultSuffix('php');
        $this->assertEquals('php', $this->stack->getDefaultSuffix());
    }

    public function testSettingDefaultSuffixStripsLeadingDot(): void
    {
        $this->stack->setDefaultSuffix('.config.php');
        $this->assertEquals('config.php', $this->stack->getDefaultSuffix());
    }
}
