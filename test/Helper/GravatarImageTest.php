<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Helper\GravatarImage;
use PHPUnit\Framework\TestCase;

use function md5;

class GravatarImageTest extends TestCase
{
    private GravatarImage $helper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->helper = new GravatarImage();
    }

    public function testThatTheGivenEmailAddressWillBeHashed(): string
    {
        $image = ($this->helper)('me@example.com');

        self::assertStringContainsString(
            md5('me@example.com'),
            $image
        );

        return $image;
    }

    /** @depends testThatTheGivenEmailAddressWillBeHashed  */
    public function testTheRatingWillDefaultToG(string $markup): void
    {
        self::assertStringContainsString(
            'r&#x3D;g&amp;',
            $markup
        );
    }

    /** @depends testThatTheGivenEmailAddressWillBeHashed  */
    public function testAnEmptyAltAttributeWillBeAddedByDefault(string $markup): void
    {
        self::assertStringContainsString(
            'alt=""',
            $markup
        );
    }

    /** @depends testThatTheGivenEmailAddressWillBeHashed  */
    public function testTheImageSizeWillBe80(string $markup): void
    {
        self::assertStringContainsString(
            's&#x3D;80',
            $markup
        );
    }

    /** @depends testThatTheGivenEmailAddressWillBeHashed  */
    public function testWidthAndHeightAttributesWillBePresent(string $markup): void
    {
        self::assertStringContainsString(
            'width="80"',
            $markup
        );
        self::assertStringContainsString(
            'height="80"',
            $markup
        );
    }

    /** @depends testThatTheGivenEmailAddressWillBeHashed  */
    public function testTheDefaultFallbackImageImageSizeWillBeMM(string $markup): void
    {
        self::assertStringContainsString(
            'd&#x3D;mm',
            $markup
        );
    }

    public function testThatAttributesCanBeAddedToTheImageMarkup(): void
    {
        $image = ($this->helper)('me@example.com', 80, ['data-foo' => 'bar']);
        self::assertStringContainsString(
            'data-foo="bar"',
            $image
        );
    }

    public function testThatTheImageSizeCanBeAltered(): string
    {
        $image = ($this->helper)('me@example.com', 123);
        self::assertStringContainsString(
            's&#x3D;123',
            $image
        );

        return $image;
    }

    /** @depends testThatTheImageSizeCanBeAltered  */
    public function testWidthAndHeightAttributesWillMatchCustomValue(string $markup): void
    {
        self::assertStringContainsString(
            'width="123"',
            $markup
        );
        self::assertStringContainsString(
            'height="123"',
            $markup
        );
    }

    public function testThatTheRatingCanBeAltered(): void
    {
        $image = ($this->helper)('me@example.com', 80, [], 'mm', 'x');
        self::assertStringContainsString(
            'r&#x3D;x&amp;',
            $image
        );
    }

    public function testThatTheDefaultImageCanBeAltered(): void
    {
        $image = ($this->helper)('me@example.com', 80, [], 'wavatar');
        self::assertStringContainsString(
            'd&#x3D;wavatar',
            $image
        );
    }

    public function testThatTheDefaultImageCanBeAnUrl(): void
    {
        $image  = ($this->helper)('me@example.com', 80, [], 'https://example.com/someimage');
        $expect = 'https&#x25;3A&#x25;2F&#x25;2Fexample.com&#x25;2Fsomeimage';
        self::assertStringContainsString($expect, $image);
    }
}
