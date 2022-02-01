<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Exception\DomainException;
use Laminas\View\Helper\Doctype;
use PHPUnit\Framework\TestCase;

class DoctypeTest extends TestCase
{
    /** @var Doctype */
    public $helper;

    /** @var string */
    public $basePath;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        Doctype::unsetDoctypeRegistry();
        $this->helper = new Doctype();
    }

    public function testDoctypeMethodReturnsObjectInstance(): void
    {
        $doctype = $this->helper->__invoke();
        $this->assertInstanceOf(Doctype::class, $doctype);
    }

    public function testPassingDoctypeSetsDoctype(): void
    {
        $doctype = $this->helper->__invoke(Doctype::XHTML1_STRICT);
        $this->assertEquals(Doctype::XHTML1_STRICT, $doctype->getDoctype());
    }

    public function testIsXhtmlReturnsTrueForXhtmlDoctypes(): void
    {
        $types = [
            Doctype::XHTML1_STRICT,
            Doctype::XHTML1_TRANSITIONAL,
            Doctype::XHTML1_FRAMESET,
            Doctype::XHTML1_RDFA,
            Doctype::XHTML1_RDFA11,
            Doctype::XHTML5,
        ];

        foreach ($types as $type) {
            $doctype = $this->helper->__invoke($type);
            $this->assertEquals($type, $doctype->getDoctype());
            $this->assertTrue($doctype->isXhtml());
        }

        // @codingStandardsIgnoreStart
        $doctype = $this->helper->__invoke('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "https://getlaminas.org/foo/DTD/xhtml1-custom.dtd">');
        // @codingStandardsIgnoreEnd
        $this->assertEquals('CUSTOM_XHTML', $doctype->getDoctype());
        $this->assertTrue($doctype->isXhtml());
    }

    public function testIsXhtmlReturnsFalseForNonXhtmlDoctypes(): void
    {
        $types = [
            Doctype::HTML4_STRICT,
            Doctype::HTML4_LOOSE,
            Doctype::HTML4_FRAMESET,
        ];

        foreach ($types as $type) {
            $doctype = $this->helper->__invoke($type);
            $this->assertEquals($type, $doctype->getDoctype());
            $this->assertFalse($doctype->isXhtml());
        }

        // @codingStandardsIgnoreStart
        $doctype = $this->helper->__invoke('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 10.0 Strict//EN" "https://getlaminas.org/foo/DTD/html10-custom.dtd">');
        // @codingStandardsIgnoreEnd
        $this->assertEquals('CUSTOM', $doctype->getDoctype());
        $this->assertFalse($doctype->isXhtml());
    }

    public function testIsHtml5(): void
    {
        foreach ([Doctype::HTML5, Doctype::XHTML5] as $type) {
            $doctype = $this->helper->__invoke($type);
            $this->assertEquals($type, $doctype->getDoctype());
            $this->assertTrue($doctype->isHtml5());
        }

        $types = [
            Doctype::HTML4_STRICT,
            Doctype::HTML4_LOOSE,
            Doctype::HTML4_FRAMESET,
            Doctype::XHTML1_STRICT,
            Doctype::XHTML1_TRANSITIONAL,
            Doctype::XHTML1_FRAMESET,
        ];

        foreach ($types as $type) {
            $doctype = $this->helper->__invoke($type);
            $this->assertEquals($type, $doctype->getDoctype());
            $this->assertFalse($doctype->isHtml5());
        }
    }

    public function testIsRdfa(): void
    {
        // ensure default registered Doctype is false
        $this->assertFalse($this->helper->isRdfa());

        $this->assertTrue($this->helper->__invoke(Doctype::XHTML1_RDFA)->isRdfa());
        $this->assertTrue($this->helper->__invoke(Doctype::XHTML1_RDFA11)->isRdfa());
        $this->assertTrue($this->helper->__invoke(Doctype::XHTML5)->isRdfa());
        $this->assertTrue($this->helper->__invoke(Doctype::HTML5)->isRdfa());

        // build-in doctypes
        $doctypes = [
            Doctype::XHTML11,
            Doctype::XHTML1_STRICT,
            Doctype::XHTML1_TRANSITIONAL,
            Doctype::XHTML1_FRAMESET,
            Doctype::XHTML_BASIC1,
            Doctype::HTML4_STRICT,
            Doctype::HTML4_LOOSE,
            Doctype::HTML4_FRAMESET,
        ];

        foreach ($doctypes as $type) {
            $this->assertFalse($this->helper->__invoke($type)->isRdfa());
        }

        // @codingStandardsIgnoreStart
        // custom doctype
        $doctype = $this->helper->__invoke('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 10.0 Strict//EN" "https://getlaminas.org/foo/DTD/html10-custom.dtd">');
        // @codingStandardsIgnoreEnd
        $this->assertFalse($doctype->isRdfa());
    }

    public function testCanRegisterCustomHtml5Doctype(): void
    {
        $doctype = $this->helper->__invoke('<!DOCTYPE html>');
        $this->assertEquals('CUSTOM', $doctype->getDoctype());
        $this->assertTrue($doctype->isHtml5());
    }

    public function testCanRegisterCustomXhtmlDoctype(): void
    {
        // @codingStandardsIgnoreStart
        $doctype = $this->helper->__invoke('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "https://getlaminas.org/foo/DTD/xhtml1-custom.dtd">');
        // @codingStandardsIgnoreEnd
        $this->assertEquals('CUSTOM_XHTML', $doctype->getDoctype());
        $this->assertTrue($doctype->isXhtml());
    }

    public function testCanRegisterCustomHtmlDoctype(): void
    {
        // @codingStandardsIgnoreStart
        $doctype = $this->helper->__invoke('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 10.0 Strict//EN" "https://getlaminas.org/foo/DTD/html10-custom.dtd">');
        // @codingStandardsIgnoreEnd
        $this->assertEquals('CUSTOM', $doctype->getDoctype());
        $this->assertFalse($doctype->isXhtml());
    }

    public function testMalformedCustomDoctypeRaisesException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('The specified doctype is malformed');
        $this->helper->__invoke('<!FOO HTML>');
    }

    public function testStringificationReturnsDoctypeString(): void
    {
        $doctype = $this->helper->__invoke(Doctype::XHTML1_STRICT);
        $string  = $doctype->__toString();
        // @codingStandardsIgnoreStart
        $this->assertEquals('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">', $string);
        // @codingStandardsIgnoreEnd
    }

    public function testDoctypeDefaultsToHtml4Loose(): void
    {
        self::assertSame(Doctype::HTML4_LOOSE, $this->helper->getDoctype());
    }
}
