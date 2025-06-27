<?php

declare(strict_types=1);

namespace LaminasTest\View\HTML;

use Laminas\View\HTML\Tag;
use PHPUnit\Framework\TestCase;

final class TagTest extends TestCase
{
    public function testEqualityWithSameAttributeOrder(): void
    {
        self::assertTrue(
            (new Tag('foo', ['bar' => 'baz', 'bing' => 'bong']))->equals(
                new Tag('foo', ['bar' => 'baz', 'bing' => 'bong']),
            ),
        );
    }

    public function testEqualityWithDifferentAttributeOrder(): void
    {
        self::assertTrue(
            (new Tag('foo', ['bar' => 'baz', 'bing' => 'bong']))->equals(
                new Tag('foo', ['bing' => 'bong', 'bar' => 'baz']),
            ),
        );
    }

    public function testEqualityWithDifferentTag(): void
    {
        self::assertFalse(
            (new Tag('foo', ['bar' => 'baz', 'bing' => 'bong']))->equals(
                new Tag('oof', ['bar' => 'baz', 'bing' => 'bong']),
            ),
        );
    }

    public function testEqualityWithDifferentAttributes(): void
    {
        self::assertFalse(
            (new Tag('foo', ['bar' => 'baz', 'bing' => 'bong']))->equals(
                new Tag('foo', ['bar' => 'baz']),
            ),
        );
    }

    public function testEqualityWithContent(): void
    {
        self::assertFalse(
            (new Tag('foo', [], 'bar'))->equals(
                new Tag('foo', [], 'baz'),
            ),
        );
        self::assertTrue(
            (new Tag('foo', [], 'bar'))->equals(
                new Tag('foo', [], 'bar'),
            ),
        );
    }

    public function testAttributeKeysAreNormalisedToLowercase(): void
    {
        $tag = new Tag('foo', ['NUTS' => 'Macadamia']);

        self::assertSame(['nuts' => 'Macadamia'], $tag->attributes);
    }

    public function testHasAttributeIsCaseInsensitive(): void
    {
        $tag = new Tag('foo', ['muppet' => 'Fozzy Bear']);

        self::assertFalse($tag->hasAttribute('bing-bong'));
        self::assertTrue($tag->hasAttribute('muppet'));
        self::assertTrue($tag->hasAttribute('MUPPET'));
    }

    public function testAttributeRetrievalIsCaseInsensitive(): void
    {
        $tag = new Tag('foo', ['muppet' => 'Kermit']);

        self::assertSame('Kermit', $tag->getAttribute('muppet'));
        self::assertSame('Kermit', $tag->getAttribute('MUPPET'));
    }

    public function testUnknownAttributeRetrievalYieldsNull(): void
    {
        $tag = new Tag('foo');

        self::assertNull($tag->getAttribute('anything'));
    }
}
