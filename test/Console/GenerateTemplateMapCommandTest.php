<?php

declare(strict_types=1);

namespace LaminasTest\View\Console;

use Laminas\View\Console\GenerateTemplateMapCommand;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;

use function unlink;

final class GenerateTemplateMapCommandTest extends TestCase
{
    private CommandTester $tester;

    protected function setUp(): void
    {
        $this->tester = new CommandTester(new GenerateTemplateMapCommand());
    }

    /** @return list<array{0: array}> */
    public static function missingArgumentProvider(): array
    {
        return [
            [[]],
            [['foo']],
        ];
    }

    /** @param array<array-key, mixed> $inputArgs */
    #[DataProvider('missingArgumentProvider')]
    public function testMissingArguments(array $inputArgs): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments');
        $this->tester->execute($inputArgs);
    }

    public function testExceptionsThrownInGeneratorConstructorPropagateAsErrorOutput(): void
    {
        $exit = $this->tester->execute([
            'templates' => __DIR__ . '/not-there',
            'config'    => __DIR__ . '/foo.php',
        ]);

        self::assertSame(Command::INVALID, $exit);
        self::assertStringContainsString(
            'The given template directory does not exist, or it is not readable',
            $this->tester->getDisplay(true),
        );
    }

    public function testOutputWillBeWrittenToAFile(): void
    {
        $configFile = __DIR__ . '/config.php';
        self::assertFileDoesNotExist($configFile);

        $expect = <<<'PHP'
            <?php
            declare(strict_types=1);
            
            return [
                'templates' => [
                    'map' => [
                        'one' => __DIR__ . '/templates/one.phtml',
                        'sub/zero' => __DIR__ . '/templates/sub/zero.phtml',
                        'two' => __DIR__ . '/templates/two.phtml',
                    ],
                ],
            ];
            
            PHP;

        try {
            $exit = $this->tester->execute([
                'templates' => __DIR__ . '/templates',
                'config'    => $configFile,
            ]);

            self::assertSame(Command::SUCCESS, $exit);
            self::assertFileExists($configFile);
            self::assertStringEqualsFile($configFile, $expect);
        } finally {
            unlink($configFile);
        }
    }
}
