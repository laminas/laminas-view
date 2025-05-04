<?php

declare(strict_types=1);

namespace LaminasTest\View\Resolver;

use Laminas\View\Resolver\PrefixPathStackResolver;
use Laminas\View\Resolver\TemplateMapResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function realpath;

#[CoversClass(PrefixPathStackResolver::class)]
final class PrefixPathStackResolverTest extends TestCase
{
    /** @var non-empty-string */
    private string $basePath;

    protected function setUp(): void
    {
        $realpath = realpath(__DIR__ . '/../_templates/prefix-path-stack-resolver');
        self::assertNotFalse($realpath);
        self::assertNotEmpty($realpath);
        $this->basePath = $realpath;
    }

    /** @return list<array{0: non-empty-string}> */
    public static function willNotResolveByDefaultProvider(): array
    {
        return [
            [__DIR__],
            [__FILE__],
            ['path/to/foo'],
            ['path/to/bar'],
        ];
    }

    /** @param non-empty-string $name */
    #[DataProvider('willNotResolveByDefaultProvider')]
    public function testResolveWithoutPathPrefixes(string $name): void
    {
        $resolver = new PrefixPathStackResolver();
        self::assertFalse($resolver->resolve($name));
    }

    public function testSuccessfulResolve(): void
    {
        $resolver = new PrefixPathStackResolver([
            'base1' => $this->basePath,
            'base2' => $this->basePath . '/baz',
        ]);

        $this->assertSame(realpath($this->basePath . '/bar.phtml'), $resolver->resolve('base1/bar'));
        $this->assertSame(realpath($this->basePath . '/baz/taz.phtml'), $resolver->resolve('base2/taz'));
    }

    public function testTheDefaultSuffixIsPassedToTheUnderlyingResolver(): void
    {
        $resolver = new PrefixPathStackResolver([
            'whatever' => $this->basePath,
        ], 'php');

        self::assertSame($this->basePath . '/foo.php', $resolver->resolve('whatever/foo'));
    }

    public function testUnprefixedNameResolvingToEmptyStringCausesException(): void
    {
        $resolver = new PrefixPathStackResolver([
            'base1' => $this->basePath,
            'base2' => $this->basePath . '/baz',
        ]);

        self::assertFalse($resolver->resolve('base2'));
    }

    public function testResolveWithCongruentPrefix(): void
    {
        $resolver = new PrefixPathStackResolver([
            'foo'    => $this->basePath,
            'foobar' => $this->basePath . '/baz',
        ]);

        $this->assertSame(realpath($this->basePath . '/bar.phtml'), $resolver->resolve('foo/bar'));
        $this->assertSame(realpath($this->basePath . '/baz/taz.phtml'), $resolver->resolve('foobar/taz'));
    }

    public function testSetCustomPathStackResolver(): void
    {
        $resolver = new PrefixPathStackResolver([
            'foo' => new TemplateMapResolver([
                '/bar' => '1111',
                '/baz' => '2222',
            ]),
        ]);

        $this->assertSame('1111', $resolver->resolve('foo/bar'));
        $this->assertSame('2222', $resolver->resolve('foo/baz'));
    }
}
