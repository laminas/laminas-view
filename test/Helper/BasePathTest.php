<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\BasePath;
use PHPUnit\Framework\TestCase;

class BasePathTest extends TestCase
{
    /** @return array<array-key, array{0: string, 1: string|null, 2: string}> */
    public function basePathDataProvider(): array
    {
        return [
            ['/foo', null, '/foo'],
            ['/foo', 'bar', '/foo/bar'],
            ['/foo', 'bar/', '/foo/bar/'],
            ['/foo', '/bar/', '/foo/bar/'],
            ['/foo/', null, '/foo'],
            ['/foo/', 'bar', '/foo/bar'],
            ['/foo/', 'bar/', '/foo/bar/'],
            ['/foo/', '/bar/', '/foo/bar/'],
            ['/foo//', '//bar', '/foo/bar'],
            ['', null, ''],
            ['', '', ''],
            ['', 'bar', '/bar'],
            ['', 'bar/', '/bar/'],
            ['', '/bar/', '/bar/'],
            ['', '//bar', '/bar'],
        ];
    }

    /** @dataProvider basePathDataProvider */
    public function testBasePathHelperYieldsExpectedOutput(string $basePath, ?string $argument, string $expect): void
    {
        $helper = new BasePath($basePath);
        self::assertEquals($expect, $helper($argument));
    }

    public function testThatAnExceptionIsThrownWhenTheBasePathIsNull(): void
    {
        $helper = new BasePath(null);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No base path provided');
        $helper();
    }
}
