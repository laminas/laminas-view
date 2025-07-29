<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Helper\Doctype;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** @psalm-import-type DoctypeID from Doctype */
final class DoctypeTest extends TestCase
{
    private Doctype $helper;

    protected function setUp(): void
    {
        $this->helper = new Doctype();
    }

    public function testInvokeReturnsSelf(): void
    {
        self::assertSame($this->helper, $this->helper->__invoke());
    }

    public function testThatCastingToStringYieldsTheDoctype(): void
    {
        self::assertSame('<!DOCTYPE html>', (string) $this->helper);
    }

    public function testTheConfiguredDeclarationIsReturnedWhenNoArgumentsGiven(): void
    {
        self::assertSame('<!DOCTYPE html>', $this->helper->doctypeDeclaration());
    }

    public function testTheDefaultDoctypeIsNotXhtml(): void
    {
        self::assertFalse($this->helper->isXhtml());
    }

    public function testTheDefaultDoctypeIsHtml5(): void
    {
        self::assertTrue($this->helper->isHtml5());
    }

    public function testTheDefaultDoctypeIsRdfa(): void
    {
        self::assertTrue($this->helper->isRdfa());
    }

    public function testAnyDoctypeCanBeRetrievedByConstant(): void
    {
        self::assertStringContainsString(
            'xhtml11/DTD/xhtml11.dtd',
            $this->helper->doctypeDeclaration(Doctype::XHTML11),
        );
    }

    /**
     * @return array<DoctypeID, array{0: DoctypeID, 1: bool, 2: bool, 3:bool}>
     */
    public static function doctypeProvider(): array
    {
        return [
            Doctype::XHTML11             => [Doctype::XHTML11, true, false, false],
            Doctype::XHTML1_STRICT       => [Doctype::XHTML1_STRICT, true, false, false],
            Doctype::XHTML1_TRANSITIONAL => [Doctype::XHTML1_TRANSITIONAL, true, false, false],
            Doctype::XHTML1_FRAMESET     => [Doctype::XHTML1_FRAMESET, true, false, false],
            Doctype::XHTML1_RDFA         => [Doctype::XHTML1_RDFA, true, false, true],
            Doctype::XHTML1_RDFA11       => [Doctype::XHTML1_RDFA11, true, false, true],
            Doctype::XHTML_BASIC1        => [Doctype::XHTML_BASIC1, true, false, false],
            Doctype::XHTML5              => [Doctype::XHTML5, false, true, true],
            Doctype::HTML4_STRICT        => [Doctype::HTML4_STRICT, false, false, false],
            Doctype::HTML4_LOOSE         => [Doctype::HTML4_LOOSE, false, false, false],
            Doctype::HTML4_FRAMESET      => [Doctype::HTML4_FRAMESET, false, false, false],
            Doctype::HTML5               => [Doctype::HTML5, false, true, true],
        ];
    }

    /**
     * @param DoctypeID $doctype
     */
    #[DataProvider('doctypeProvider')]
    public function testIsMethodsForAllDoctypes(
        string $doctype,
        bool $isXhtml,
        bool $isHtml5,
        bool $isRdfa,
    ): void {
        self::assertSame($isXhtml, $this->helper->isXhtml($doctype));
        self::assertSame($isHtml5, $this->helper->isHtml5($doctype));
        self::assertSame($isRdfa, $this->helper->isRdfa($doctype));
    }

    public function testExceptionThrownFetchingDeclarationForAnUnknownDoctype(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->helper->doctypeDeclaration('Foo');
    }
}
