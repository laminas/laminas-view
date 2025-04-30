<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\Escaper;

use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Helper\EscapeHtml;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

final class AbstractHelperTest extends TestCase
{
    public function testExceptionThrownEscapingAnObjectWithRecursionOff(): void
    {
        $helper = new EscapeHtml();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Object provided to Escape helper, but flags do not allow recursion');

        $helper->__invoke(new stdClass());
    }

    public function testExceptionThrownEscapingAnArrayWithRecursionOff(): void
    {
        $helper = new EscapeHtml();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Array provided to Escape helper, but flags do not allow recursion');

        $helper->__invoke(['foo']);
    }

    /** @return array<string, array{0: mixed}> */
    public static function unEscaped(): array
    {
        return [
            'null'    => [null],
            'integer' => [42],
            'float'   => [3.142],
            'bool'    => [true],
        ];
    }

    #[DataProvider('unEscaped')]
    public function testNonStringScalarsAreReturnedAsIs(mixed $value): void
    {
        $helper = new EscapeHtml();

        self::assertSame($value, $helper->__invoke($value));
    }
}
