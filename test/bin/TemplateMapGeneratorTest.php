<?php

declare(strict_types=1);

namespace LaminasTest\View\bin;

use PHPUnit\Framework\TestCase;

use function chdir;
use function sprintf;

use const PHP_EOL;

final class TemplateMapGeneratorTest extends TestCase
{
    private function commandOutput(string $args): string
    {
        chdir(__DIR__);

        $command = sprintf(__DIR__ . '/../../bin/templatemap_generator.php %s', $args);

        /** @psalm-suppress ForbiddenCode */
        $output  = `$command`; // phpcs:ignore

        self::assertIsString($output);

        return $output;
    }

    public function testThatTheTemplateMapGeneratorProducesTheExpectedOutput(): void
    {
        $output = $this->commandOutput('templates');

        self::assertStringStartsWith('<?php' . PHP_EOL . 'return [', $output);
        self::assertStringEndsWith('];', $output);
        self::assertStringContainsString("'one' => __DIR__ . '/templates/one.phtml',", $output);
        self::assertStringContainsString("'two' => __DIR__ . '/templates/two.phtml',", $output);

        self::assertStringNotContainsString('ignored.txt', $output);
        self::assertStringNotContainsString('ignored', $output);
    }

    public function testThatHelpTextWillBeOutputWhenRequested(): void
    {
        $output = $this->commandOutput('-h');
        self::assertStringContainsString('Generate template maps.', $output);

        $output = $this->commandOutput('--help');
        self::assertStringContainsString('Generate template maps.', $output);
    }

    public function testThatTheMapGeneratorAcceptsAListOfFileNames(): void
    {
        $output = $this->commandOutput('. some/foo.txt bar.phtml');
        self::assertStringStartsWith('<?php' . PHP_EOL . 'return [', $output);
        self::assertStringEndsWith('];', $output);
        self::assertStringContainsString("'some/foo' => __DIR__ . '/some/foo.txt',", $output);
        self::assertStringContainsString("'bar' => __DIR__ . '/bar.phtml',", $output);
    }
}
