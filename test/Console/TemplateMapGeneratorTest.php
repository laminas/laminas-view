<?php

declare(strict_types=1);

namespace LaminasTest\View\Console;

use Laminas\View\Console\TemplateMapGenerator;
use Laminas\View\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

use function sprintf;

final class TemplateMapGeneratorTest extends TestCase
{
    public function testExpectedOutputWhenConfigIsNestedOutsideTemplateDirectory(): void
    {
        $generator = new TemplateMapGenerator(
            __DIR__ . '/templates',
            __DIR__ . '/../../src/config.php',
        );

        $expect = <<<'PHP'
            <?php
            declare(strict_types=1);
            
            return [
                'templates' => [
                    'map' => [
                        'one' => __DIR__ . '/../test/Console/templates/one.phtml',
                        'sub/zero' => __DIR__ . '/../test/Console/templates/sub/zero.phtml',
                        'two' => __DIR__ . '/../test/Console/templates/two.phtml',
                    ],
                ],
            ];
            
            PHP;

        $output = $generator();

        self::assertSame($expect, $output);
    }

    public function testExpectedOutputWhenConfigIsInsideTemplateDirectory(): void
    {
        $generator = new TemplateMapGenerator(
            __DIR__ . '/templates',
            __DIR__ . '/templates/config.php',
        );

        $expect = <<<'PHP'
            <?php
            declare(strict_types=1);
            
            return [
                'templates' => [
                    'map' => [
                        'one' => __DIR__ . '/one.phtml',
                        'sub/zero' => __DIR__ . '/sub/zero.phtml',
                        'two' => __DIR__ . '/two.phtml',
                    ],
                ],
            ];
            
            PHP;

        $output = $generator();

        self::assertSame($expect, $output);
    }

    public function testExpectedOutputWhenConfigIsBelowTemplateDirectory(): void
    {
        $generator = new TemplateMapGenerator(
            __DIR__ . '/templates',
            __DIR__ . '/templates/ignored/config.php',
        );

        $expect = <<<'PHP'
            <?php
            declare(strict_types=1);
            
            return [
                'templates' => [
                    'map' => [
                        'one' => __DIR__ . '/../one.phtml',
                        'sub/zero' => __DIR__ . '/../sub/zero.phtml',
                        'two' => __DIR__ . '/../two.phtml',
                    ],
                ],
            ];
            
            PHP;

        $output = $generator();

        self::assertSame($expect, $output);
    }

    public function testTheDirectoryToScanMustExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'The given template directory does not exist, or it is not readable: %s',
            __DIR__ . '/not-there',
        ));

        new TemplateMapGenerator(
            __DIR__ . '/not-there',
            __DIR__ . '/templates/ignored/config.php',
        );
    }

    public function testTheConfigFileShouldBeAPhpFile(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A .php file name extension is required for the target file');

        new TemplateMapGenerator(
            __DIR__,
            __DIR__ . '/config.html',
        );
    }

    public function testTheConfigFileParentDirectoryShouldBeWritable(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'The target directory is not writable: %s',
            __DIR__ . '/not-there'
        ));

        new TemplateMapGenerator(
            __DIR__,
            __DIR__ . '/not-there/config.php',
        );
    }
}
