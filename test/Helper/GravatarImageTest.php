<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\Escaper\Escaper;
use Laminas\View\Helper\GravatarImage;
use PHPUnit\Framework\TestCase;

use function md5;
use function sprintf;

class GravatarImageTest extends TestCase
{
    private GravatarImage $helper;
    private Escaper $escaper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->helper  = new GravatarImage();
        $this->escaper = new Escaper();
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
        $expect = $this->escaper->escapeHtmlAttr(sprintf(
            'r=%s',
            GravatarImage::RATING_G
        ));

        self::assertStringContainsString(
            $expect,
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
        $expect = $this->escaper->escapeHtmlAttr('s=80');

        self::assertStringContainsString(
            $expect,
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
    public function testTheDefaultFallbackImageImageSizeWillBeMP(string $markup): void
    {
        $expect = $this->escaper->escapeHtmlAttr(sprintf(
            'd=%s',
            GravatarImage::DEFAULT_MP
        ));

        self::assertStringContainsString(
            $expect,
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
        $image  = ($this->helper)('me@example.com', 123);
        $expect = $this->escaper->escapeHtmlAttr('s=123');

        self::assertStringContainsString(
            $expect,
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
        $image  = ($this->helper)('me@example.com', 80, [], GravatarImage::DEFAULT_MP, GravatarImage::RATING_X);
        $expect = $this->escaper->escapeHtmlAttr(sprintf(
            'r=%s',
            GravatarImage::RATING_X
        ));

        self::assertStringContainsString(
            $expect,
            $image
        );
    }

    public function testThatTheDefaultImageCanBeAltered(): void
    {
        $image  = ($this->helper)('me@example.com', 80, [], GravatarImage::DEFAULT_WAVATAR);
        $expect = $this->escaper->escapeHtmlAttr(sprintf(
            'd=%s',
            GravatarImage::DEFAULT_WAVATAR
        ));

        self::assertStringContainsString(
            $expect,
            $image
        );
    }

    public function testThatTheDefaultImageCanBeAnUrl(): void
    {
        $customImage = 'https://example.com/someimage';
        $image       = ($this->helper)('me@example.com', 80, [], $customImage);
        $expect      = $this->escaper->escapeHtmlAttr(sprintf(
            'd=%s',
            $this->escaper->escapeUrl($customImage)
        ));

        self::assertStringContainsString($expect, $image);
    }
}
